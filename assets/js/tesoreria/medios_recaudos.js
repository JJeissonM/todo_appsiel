$("#teso_motivo_id option:first").next().attr("selected", "selected");

function usa_modal_botones_medios_pago() {
  return $("#usar_modal_botones_medios_pago").val() === "1";
}

function get_modal_botones_medios_pago_data() {
  var raw = $("#modal_botones_medios_pago_data_json").text();

  if (raw === undefined || raw === "") {
    return { medios: [], destinos: {} };
  }

  try {
    return JSON.parse(raw);
  } catch (error) {
    return { medios: [], destinos: {} };
  }
}

function get_destinos_medio_seleccionado() {
  if (!usa_modal_botones_medios_pago()) {
    return { cajas: [], cuentas: [] };
  }

  var medioSeleccionado = $("#teso_medio_recaudo_id").val().split("-")[0];
  var data = get_modal_botones_medios_pago_data();

  if (
    medioSeleccionado === "" ||
    data.destinos === undefined ||
    data.destinos[medioSeleccionado] === undefined
  ) {
    return { cajas: [], cuentas: [] };
  }

  return data.destinos[medioSeleccionado];
}

function get_primer_medio_recaudo_por_texto(textoBuscado) {
  var encontrado = null;

  $("#grupo_botones_teso_medio_recaudo")
    .find('.btn_pos_payment_option[data-option-type="medio"]')
    .each(function () {
      var label = ($(this).data("label") || "").toString().toLowerCase();
      if (label === textoBuscado.toLowerCase() && encontrado === null) {
        encontrado = {
          value: $(this).data("value"),
          label: $(this).data("label")
        };
      }
    });

  return encontrado;
}

function get_medio_recaudo_por_shortcut(shortcut) {
  var encontrado = null;

  $("#grupo_botones_teso_medio_recaudo")
    .find('.btn_pos_payment_option[data-option-type="medio"]')
    .each(function () {
      if (String($(this).data("shortcut")) === String(shortcut) && encontrado === null) {
        encontrado = $(this);
      }
    });

  return encontrado;
}

function marcar_boton_seleccionado(selectorGrupo, value) {
  $(selectorGrupo)
    .find(".btn_pos_payment_option")
    .removeClass("btn-selected");

  $(selectorGrupo)
    .find('.btn_pos_payment_option[data-value="' + value + '"]')
    .addClass("btn-selected");
}

function render_botones_destino(selectorGrupo, opciones, tipo) {
  var html = "";

  opciones.forEach(function (opcion) {
    html +=
      '<button type="button" class="btn btn-default btn_pos_payment_option" ' +
      'data-option-type="' +
      tipo +
      '" ' +
      'data-id="' +
      opcion.id +
      '" ' +
      'data-value="' +
      opcion.value +
      '" ' +
      'data-label="' +
      opcion.label +
      '">' +
      opcion.label +
      "</button>";
  });

  $(selectorGrupo).html(html);
}

function reset_destinos_medio_pago() {
  $("#teso_caja_id").val("");
  $("#teso_cuenta_bancaria_id").val("");
  $("#grupo_botones_teso_caja").html("");
  $("#grupo_botones_teso_cuenta_bancaria").html("");
  $("#div_caja").hide();
  $("#div_cuenta_bancaria").hide();
}

function validar_destino_medio_pago_seleccionado() {
  if (!usa_modal_botones_medios_pago()) {
    return true;
  }

  var destinos = get_destinos_medio_seleccionado();
  var requiereCaja = destinos.cajas.length > 0;
  var requiereCuenta = destinos.cuentas.length > 0;

  if (!requiereCaja && !requiereCuenta) {
    return false;
  }

  return $("#teso_caja_id").val() !== "" || $("#teso_cuenta_bancaria_id").val() !== "";
}

function actualizar_destinos_por_medio() {
  var destinos = get_destinos_medio_seleccionado();

  reset_destinos_medio_pago();

  if (destinos.cajas.length > 0) {
    render_botones_destino("#grupo_botones_teso_caja", destinos.cajas, "caja");
    $("#div_caja").show();
  }

  if (destinos.cuentas.length > 0) {
    render_botones_destino(
      "#grupo_botones_teso_cuenta_bancaria",
      destinos.cuentas,
      "cuenta"
    );
    $("#div_cuenta_bancaria").show();
  }

  if (destinos.cajas.length === 1 && destinos.cuentas.length === 0) {
    var caja = destinos.cajas[0];
    $("#teso_caja_id").val(caja.value);
    marcar_boton_seleccionado("#grupo_botones_teso_caja", caja.value);
  }

  if (destinos.cuentas.length === 1 && destinos.cajas.length === 0) {
    var cuenta = destinos.cuentas[0];
    $("#teso_cuenta_bancaria_id").val(cuenta.value);
    marcar_boton_seleccionado("#grupo_botones_teso_cuenta_bancaria", cuenta.value);
  }

  if (destinos.cajas.length > 0 && $("#teso_caja_id").val() === "") {
    var primeraCaja = destinos.cajas[0];
    $("#teso_caja_id").val(primeraCaja.value);
    marcar_boton_seleccionado("#grupo_botones_teso_caja", primeraCaja.value);
  }

  if (destinos.cuentas.length > 0 && $("#teso_cuenta_bancaria_id").val() === "") {
    var primeraCuenta = destinos.cuentas[0];
    $("#teso_cuenta_bancaria_id").val(primeraCuenta.value);
    marcar_boton_seleccionado(
      "#grupo_botones_teso_cuenta_bancaria",
      primeraCuenta.value
    );
  }
}

function seleccionar_medio_recaudo_por_boton($botonMedio) {
  if (!$botonMedio || !$botonMedio.length) {
    return false;
  }

  var value = $botonMedio.data("value");
  $("#teso_medio_recaudo_id").val(value);
  marcar_boton_seleccionado("#grupo_botones_teso_medio_recaudo", value);
  actualizar_destinos_por_medio();

  if (validar_destino_medio_pago_seleccionado()) {
    habilitar_text($("#valor_total"));
    $("#valor_total").focus();
  } else {
    deshabilitar_text($("#valor_total"));
    Swal.fire({
      icon: "warning",
      title: "Alerta",
      text: "El medio de pago seleccionado no tiene cajas o cuentas bancarias disponibles."
    });
  }

  return true;
}

function on_change_medio_recaudo_select() {
  var valor = $("#teso_medio_recaudo_id").val().split("-");

  if (valor == "") {
    $("#div_cuenta_bancaria").hide();
    $("#div_caja").hide();
    deshabilitar_text($("#valor_total"));
    $("#teso_medio_recaudo_id").focus();
    alert("Debe escoger un medio de recaudo");
    return false;
  }

  var texto_motivo = $("#teso_motivo_id").html();

  $("#teso_caja_id option").removeAttr("selected");
  $("#teso_cuenta_bancaria_id option").removeAttr("selected");

  if (texto_motivo == "") {
    alert(
      "No se han creado motivos para el TIPO DE RECAUDO selecccionado. Debe crear al menos un MOTIVO para cada TIPO DE RECAUDO. No puede continuar."
    );
    $("#teso_tipo_motivo").focus();
  } else {
    $("#div_cuenta_bancaria").hide();
    $("#div_caja").show();

    var position = 2;
    if ($("#teso_caja_id option").length == 1) {
      position = 1;
    }
    $("#teso_caja_id option:nth-child(" + position + ")").prop("selected", true);

    $("#teso_cuenta_bancaria_id option:nth-child(2)").prop("selected", true);

    if (valor[1] == "Tarjeta bancaria") {
      $("#div_caja").hide();
      $("#div_cuenta_bancaria").show();
    }

    habilitar_text($("#valor_total"));
    $("#valor_total").focus();
  }
}

function validar_valor_y_destino() {
  var ok = false;
  if ($.isNumeric($("#valor_total").val())) {
    $("#valor_total").attr("style", "background-color:white;");
    ok = true;
  } else {
    $("#valor_total").attr("style", "background-color:#FF8C8C;");
    $("#valor_total").focus();
  }

  if (usa_modal_botones_medios_pago()) {
    if (!validar_destino_medio_pago_seleccionado()) {
      $("#btn_agregar").hide();
      return false;
    }

    return ok;
  }

  if ($("#teso_medio_recaudo_id").val() == "1-Efectivo") {
    $("#teso_caja_id").attr("style", "background-color:white;");
    if ($("#teso_caja_id").val() === null || $("#teso_caja_id").val() === "") {
      $("#teso_caja_id").attr("style", "background-color:#FF8C8C;");
      $("#btn_agregar").hide();
      return false;
    }
  } else {
    $("#teso_cuenta_bancaria_id").attr("style", "background-color:white;");
    if (
      $("#teso_cuenta_bancaria_id").val() === null ||
      $("#teso_cuenta_bancaria_id").val() === ""
    ) {
      $("#teso_cuenta_bancaria_id").attr("style", "background-color:#FF8C8C;");
      $("#btn_agregar").hide();
      return false;
    }
  }

  return ok;
}

function get_textos_medios_pago() {
  if (usa_modal_botones_medios_pago()) {
    var textoMedio = $("#grupo_botones_teso_medio_recaudo")
      .find('.btn-selected[data-option-type="medio"]')
      .first();

    var textoCaja = $("#grupo_botones_teso_caja")
      .find('.btn-selected[data-option-type="caja"]')
      .first();

    var textoCuenta = $("#grupo_botones_teso_cuenta_bancaria")
      .find('.btn-selected[data-option-type="cuenta"]')
      .first();

    return {
      medio: [
        $("#teso_medio_recaudo_id").val().split("-")[0],
        textoMedio.data("label") || ""
      ],
      caja:
        $("#teso_caja_id").val() === ""
          ? [0, ""]
          : [$("#teso_caja_id").val().split("-")[0], textoCaja.data("label") || ""],
      cuenta:
        $("#teso_cuenta_bancaria_id").val() === ""
          ? [0, ""]
          : [
              $("#teso_cuenta_bancaria_id").val().split("-")[0],
              textoCuenta.data("label") || ""
            ]
    };
  }

  var medioRecaudo = $("#teso_medio_recaudo_id").val().split("-");
  var textoMedioRecaudo = [
    medioRecaudo[0],
    $("#teso_medio_recaudo_id option:selected").text()
  ];

  if (medioRecaudo[1] == "Tarjeta bancaria") {
    return {
      medio: textoMedioRecaudo,
      caja: [0, ""],
      cuenta: [
        $("#teso_cuenta_bancaria_id").val(),
        $("#teso_cuenta_bancaria_id option:selected").text()
      ]
    };
  }

  return {
    medio: textoMedioRecaudo,
    caja: [$("#teso_caja_id").val(), $("#teso_caja_id option:selected").text()],
    cuenta: [0, ""]
  };
}

/*
			**	Abrir formulario de medios de pago
			*/
$("#btn_nuevo").click(function (event) {
  event.preventDefault();
  reset_form_registro();
  $("#recaudoModal").modal({ backdrop: "static", keyboard: "true" });
});

// Al mostrar la ventana modal
$("#recaudoModal").on("shown.bs.modal", function () {
  if (usa_modal_botones_medios_pago()) {
    $("#valor_total").focus();
    return;
  }

  $("#teso_medio_recaudo_id").focus();
});

$(document).on("keydown", function (event) {
  var key = event.which || event.keyCode;
  if (key !== 27) {
    return;
  }

  if ($("#recaudoModal").is(":visible")) {
    $("#recaudoModal").modal("hide");
  }
});

// Al OCULTAR la ventana modal
$("#recaudoModal").on("hidden.bs.modal", function () {
  if ($("#btn_guardar_factura").length) {
    $("#btn_guardar_factura").focus();
    return;
  }

  $("#btn_continuar2").focus();
});

if (!usa_modal_botones_medios_pago()) {
  $("#teso_medio_recaudo_id").change(function () {
    on_change_medio_recaudo_select();
  });

  $("#teso_cuenta_bancaria_id").change(function () {
    $("#valor_total").focus();
  });

  $("#teso_caja_id").change(function () {
    $("#valor_total").focus();
  });
}

$(document).on("click", ".btn_pos_payment_option", function (event) {
  event.preventDefault();

  if (!usa_modal_botones_medios_pago()) {
    return;
  }

  var tipo = $(this).data("option-type");

  if (tipo === "medio") {
    seleccionar_medio_recaudo_por_boton($(this));
    return;
  }

  var value = $(this).data("value");

  if (tipo === "caja") {
    $("#teso_caja_id").val(value);
    $("#teso_cuenta_bancaria_id").val("");
    marcar_boton_seleccionado("#grupo_botones_teso_caja", value);
    $("#grupo_botones_teso_cuenta_bancaria")
      .find(".btn_pos_payment_option")
      .removeClass("btn-selected");
    $("#valor_total").focus();
    return;
  }

  $("#teso_cuenta_bancaria_id").val(value);
  $("#teso_caja_id").val("");
  marcar_boton_seleccionado("#grupo_botones_teso_cuenta_bancaria", value);
  $("#grupo_botones_teso_caja")
    .find(".btn_pos_payment_option")
    .removeClass("btn-selected");
  $("#valor_total").focus();
});

$(document).on("keydown", function (event) {
  if (!usa_modal_botones_medios_pago()) {
    return true;
  }

  if (!$("#recaudoModal").is(":visible")) {
    return true;
  }

  if (!event.ctrlKey || event.altKey || event.metaKey) {
    return true;
  }

  var key = (event.key || "").toString();
  if (!/^[0-9]$/.test(key)) {
    return true;
  }

  var shortcut = parseInt(key, 10);
  if (shortcut === 0) {
    return true;
  }

  var $medio = get_medio_recaudo_por_shortcut(shortcut);
  if (!$medio || !$medio.length) {
    return true;
  }

  event.preventDefault();
  event.stopPropagation();
  seleccionar_medio_recaudo_por_boton($medio);
  return false;
});

$("#valor_total").keyup(function (event) {
  event.preventDefault();

  var ok = validar_valor_y_destino();

  var x = event.which || event.keyCode;
  if (x === 13 && ok) {
    $("#btn_agregar").show();
    $("#btn_agregar").focus();
  }

  return false;
});

/*
    ** Al presionar el botón agregar (ingreso de medios de recaudo)
    */
$("#btn_agregar").click(function (event) {
  event.preventDefault();

  if ($("#teso_motivo_id").val() == 0 || $("#teso_motivo_id").val() == "") {
    $("#teso_motivo_id").focus();
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "Debe seleccionar un Motivo."
    });

    return false;
  }

  if ($("#teso_medio_recaudo_id").val() == "") {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "Debe seleccionar un Medio de pago."
    });
    return false;
  }

  if (!validar_destino_medio_pago_seleccionado()) {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "Debe seleccionar una Caja o una Cuenta Bancaria."
    });
    return false;
  }

  var valorTotal = $("#valor_total").val();

  if ($.isNumeric(valorTotal) && valorTotal > 0) {
    var textos = get_textos_medios_pago();
    var textoMotivo = [
      $("#teso_motivo_id").val(),
      $("#teso_motivo_id option:selected").text()
    ];

    var btnBorrar =
      "<button type='button' class='btn btn-danger btn-xs btn_eliminar_linea_medio_recaudo'><i class='fa fa-btn fa-trash'></i></button>";

    var celdaValorTotal = '<td class="valor_total">$' + valorTotal + "</td>";

    $("#ingreso_registros_medios_recaudo")
      .find("tbody:last")
      .append(
        "<tr>" +
          '<td><span style="color:white;">' +
          textos.medio[0] +
          '-</span><span>' +
          textos.medio[1] +
          "</span></td>" +
          '<td><span style="color:white;">' +
          textoMotivo[0] +
          '-</span><span>' +
          textoMotivo[1] +
          "</span></td>" +
          '<td><span style="color:white;">' +
          textos.caja[0] +
          '-</span><span>' +
          textos.caja[1] +
          "</span></td>" +
          '<td><span style="color:white;">' +
          textos.cuenta[0] +
          '-</span><span>' +
          textos.cuenta[1] +
          "</span></td>" +
          celdaValorTotal +
          "<td>" +
          btnBorrar +
          "</td>" +
          "</tr>"
      );

    calcular_totales_medio_recaudos();
    reset_form_registro();
    $("#btn_guardar").show();
  } else {
    $("#valor_total").attr("style", "background-color:#FF8C8C;");
    $("#valor_total").focus();

    alert("Datos incorrectos o incompletos. Por favor verifique.");

    if ($("#total_valor_total").text() == "$0.00") {
      $("#btn_continuar2").hide();
    }
  }
});

/*
    ** Al eliminar una fila
    */
$(document).on("click", ".btn_eliminar_linea_medio_recaudo", function (event) {
  event.preventDefault();
  var fila = $(this).closest("tr");

  if (fila.find("span").eq(0).text() == "0-") {
    $("#object_anticipos").val("null");
  }

  fila.remove();
  calcular_totales_medio_recaudos();
  if ($("#total_valor_total").text() == "$0.00") {
    $("#efectivo_recibido").removeAttr("readonly");
  }
  $("#btn_nuevo").show();
});

function calcular_totales_medio_recaudos() {
  var sum = 0.0;
  $(".valor_total").each(function () {
    var cadena = $(this).text();
    sum += parseFloat(cadena.substring(1));
  });

  $("#total_valor_total").text("$" + sum.toFixed(2));
  $("#suma_cambio").val(sum);
  $("#total_valor_total").actualizar_medio_recaudo();
}

function habilitar_text($control) {
  $control.removeAttr("disabled");
  $control.attr("style", "background-color:white;");
}

function deshabilitar_text($control) {
  $control.attr("style", "background-color:#ECECE5;");
  $control.attr("disabled", "disabled");
}

function reset_form_registro() {
  $("#form_registro input[type='text']").val("");
  $("#valor_total").val("");
  $("#btn_agregar").hide();

  if (usa_modal_botones_medios_pago()) {
    $("#teso_medio_recaudo_id").val("");
    reset_destinos_medio_pago();
    $("#grupo_botones_teso_medio_recaudo")
      .find(".btn_pos_payment_option")
      .removeClass("btn-selected");
    deshabilitar_text($("#valor_total"));

    var medioEfectivo = get_primer_medio_recaudo_por_texto("efectivo");
    if (medioEfectivo !== null) {
      $("#teso_medio_recaudo_id").val(medioEfectivo.value);
      marcar_boton_seleccionado(
        "#grupo_botones_teso_medio_recaudo",
        medioEfectivo.value
      );
      actualizar_destinos_por_medio();
      if (validar_destino_medio_pago_seleccionado()) {
        habilitar_text($("#valor_total"));
        $("#valor_total").focus();
      }
    }

    return;
  }

  $("#teso_medio_recaudo_id").val("");
  $("#teso_cuenta_bancaria_id").val("");
  $("#teso_caja_id").val("");
  $("#div_caja").hide();
  $("#div_cuenta_bancaria").hide();
  $("#teso_medio_recaudo_id").focus();
}

function habilitar_campos_form_create() {
  $("#fecha").removeAttr("disabled");
  $(".custom-combobox").show();

  $("#id_tercero").attr("name", "id_tercero");

  $("#core_tercero_id").hide();
  $("#core_tercero_id").removeAttr("disabled");
  $("#core_tercero_id").attr("name", "core_tercero_id");

  $("#teso_tipo_motivo").removeAttr("disabled");
}

if (usa_modal_botones_medios_pago()) {
  deshabilitar_text($("#valor_total"));
}
