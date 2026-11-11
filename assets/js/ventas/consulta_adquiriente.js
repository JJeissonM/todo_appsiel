/**
 * Consulta lo que la DIAN tiene del adquiriente al digitar su identificación, en los
 * formularios de creación de clientes (Ventas y modal del POS).
 *
 * La DIAN responde con lo que otros facturadores informaron en sus facturas, no con el
 * RUT: un número mal digitado puede traer datos. Por eso solo se sugiere, el usuario
 * decide si los usa, y ningún resultado impide guardar.
 */
(function ($) {
  var CAMPOS_NUMERO = "#numero_identificacion, #numero_identificacion_aux";
  var CONSUMIDOR_FINAL = "222222222222";
  // Los mismos caracteres que ventas/clientes.js rechaza en los nombres.
  var CARACTERES_NO_PERMITIDOS = /[!@#$%^&*()_+\={}\[\]:;"'<>,.?/\|~]/g;

  var COLORES_PANEL = {
    info: { backgroundColor: "#d9edf7", borderColor: "#bce8f1", color: "#31708f" },
    aviso: { backgroundColor: "#fcf8e3", borderColor: "#faebcc", color: "#8a6d3b" },
    error: { backgroundColor: "#f2dede", borderColor: "#ebccd1", color: "#a94442" },
  };

  var peticion = null;

  /**
   * El POS tiene en la página otros campos con estos mismos id (#numero_identificacion,
   * #descripcion, #email...): todo se busca dentro del formulario del campo digitado.
   */
  function enFormulario(campo, selector) {
    return campo.closest("form").find(selector);
  }

  function quitarPanel() {
    $("#panel_consulta_adquiriente").remove();
  }

  function mostrarPanel(campo, tipo, partes) {
    quitarPanel();

    // Colores propios: el tema de Appsiel cambia los de .alert-info por morado.
    // El .form-group mide 0 de alto (sus hijos flotan), de ahí el clear.
    var panel = $('<div id="panel_consulta_adquiriente" class="alert"></div>')
      .css($.extend({ clear: "both", margin: "5px 15px 10px", padding: "8px 12px" }, COLORES_PANEL[tipo]))
      .append(partes);

    var grupo = campo.closest(".form-group");
    (grupo.length ? grupo : campo.parent()).after(panel);
  }

  function linea(texto) {
    return $("<div></div>").text(texto);
  }

  function boton(texto, alHacerClick) {
    return $('<button type="button" class="btn btn-default btn-xs" style="margin-top: 5px;"></button>')
      .text(texto)
      .on("click", alHacerClick);
  }

  function dvNoCoincide(campo, respuesta) {
    var digitado = $.trim(enFormulario(campo, "#digito_verificacion").val());

    return digitado !== "" && respuesta.verification_digit && digitado !== String(respuesta.verification_digit);
  }

  function lineaDv(respuesta) {
    return linea("El dígito de verificación no corresponde a este NIT; debería ser " + respuesta.verification_digit + ".");
  }

  function usarDatos(campo, respuesta) {
    var nombre = $.trim(String(respuesta.name || "").replace(CARACTERES_NO_PERMITIDOS, "").replace(/\s+/g, " "));

    if (nombre !== "") {
      enFormulario(campo, "#descripcion").val(nombre);
    }
    if (respuesta.email) {
      enFormulario(campo, "#email").val(respuesta.email);
    }
    if (respuesta.verification_digit !== null && respuesta.verification_digit !== undefined) {
      enFormulario(campo, "#digito_verificacion").val(respuesta.verification_digit);
    }

    quitarPanel();
  }

  function corregirNit(campo, sugerido) {
    campo.val(sugerido.identification_number);
    enFormulario(campo, "#digito_verificacion").val(sugerido.verification_digit);
    consultar(campo);
  }

  function mostrarRespuesta(campo, respuesta) {
    var sugerido = respuesta.suggested_identification;

    if ($.inArray("nit_con_dv", respuesta.warnings || []) !== -1 && sugerido) {
      mostrarPanel(campo, "error", [
        linea(
          "Parece el NIT " + sugerido.identification_number + " con el dígito de verificación " +
          sugerido.verification_digit + " al final. El NIT se digita sin el dígito de verificación."
        ),
        boton("Corregir", function () {
          corregirNit(campo, sugerido);
        }),
      ]);
      return;
    }

    switch (respuesta.status) {
      case "existe":
        mostrarPanel(campo, "aviso", [linea("Existe este número para " + $.trim(respuesta.name))]);
        break;

      case "found":
        // Son datos de facturas anteriores, no del RUT: el usuario los confirma con el cliente.
        var partes = [
          linea("Datos DIAN: " + respuesta.name + (respuesta.email ? " · " + respuesta.email : "")),
          linea("¡Recuerda confirmar con el cliente!"),
        ];
        if (dvNoCoincide(campo, respuesta)) {
          partes.push(lineaDv(respuesta));
        }
        partes.push(boton("Usar datos", function () {
          usarDatos(campo, respuesta);
        }));
        mostrarPanel(campo, dvNoCoincide(campo, respuesta) ? "aviso" : "info", partes);
        break;

      case "not_found":
        var mensajes = [linea("La DIAN no tiene datos para este número. Revise que esté bien digitado.")];
        if (dvNoCoincide(campo, respuesta)) {
          mensajes.push(lineaDv(respuesta));
        }
        mostrarPanel(campo, "aviso", mensajes);
        break;

      case "deshabilitada":
        quitarPanel();
        break;

      default:
        mostrarPanel(campo, "info", [linea("No se pudo consultar a la DIAN. Puede guardar normalmente.")]);
    }
  }

  function consultar(campo) {
    var numero = $.trim(campo.val()).replace(/[\s.,]/g, "");
    var tipo = enFormulario(campo, "#id_tipo_documento_id").val();
    var clave = tipo + "/" + numero;

    // Se guarda en el campo porque el modal del POS lo crea de nuevo cada vez que se abre.
    if (campo.data("adquiriente_consultado") === clave) {
      return;
    }

    if (peticion) {
      peticion.abort();
      peticion = null;
    }

    campo.data("adquiriente_consultado", clave);
    quitarPanel();

    if (!tipo || numero.length < 5 || numero === CONSUMIDOR_FINAL) {
      return;
    }

    mostrarPanel(campo, "info", [linea("Consultando a la DIAN...")]);

    peticion = $.ajax({
      url: url_raiz + "/fe_consultar_adquiriente/" + encodeURIComponent(tipo) + "/" + encodeURIComponent(numero),
      type: "GET",
      dataType: "json",
      timeout: 10000,
      success: function (respuesta) {
        peticion = null;
        mostrarRespuesta(campo, respuesta);
      },
      error: function (xhr, estado) {
        if (estado === "abort") {
          return;
        }
        peticion = null;
        mostrarRespuesta(campo, { status: "no_disponible" });
      },
    });
  }

  $(document).on("blur", CAMPOS_NUMERO, function () {
    consultar($(this));
  });

  $(document).on("change", "#id_tipo_documento_id", function () {
    var campo = $(this).closest("form").find(CAMPOS_NUMERO).first();

    if (campo.length) {
      consultar(campo);
    }
  });
})(jQuery);
