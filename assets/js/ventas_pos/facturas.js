// Variables de cada línea de ingresos de registros.
var producto_id,
  inv_producto_id,
  inv_bodega_id,
  inv_motivo_id,
  unidad_medida;

var ventana_factura;
var numero_linea = 0;

$("#teso_motivo_id").val(1);
$("#teso_caja_id").val($("#caja_pdv_default_id").val());

// Se debe llamar desde el DIV con ID total_valor_total
$.fn.actualizar_medio_recaudo = function () {
  var texto_total_recaudos = parseFloat(this.html().substring(1));

  $.fn.calcular_total_cambio(texto_total_recaudos);

  $("#efectivo_recibido").val(texto_total_recaudos);
  $("#total_efectivo_recibido").val(texto_total_recaudos);

  $.fn.set_label_efectivo_recibido(texto_total_recaudos);

  $.fn.cambiar_estilo_div_total_cambio();

  $.fn.activar_boton_guardar_factura();

  if (texto_total_recaudos == 0) {
    $("#efectivo_recibido").val("");
    $("#efectivo_recibido").removeAttr("readonly");
  } else {
    $("#efectivo_recibido").attr("readonly", "readonly");
  }

  $.fn.set_valor_pendiente_ingresar_medios_recaudos();
};

//
$.fn.calcular_total_cambio = function (efectivo_recibido) {
  total_cambio =
    ($.fn.redondear_a_centena(parseFloat($("#valor_total_factura").val())) -
      parseFloat(efectivo_recibido)) *
    -1;

  // Label
  $("#total_cambio").text(
    "$ " + new Intl.NumberFormat("de-DE").format(total_cambio.toFixed(0))
  );
  // Input hidden
  $("#valor_total_cambio").val(total_cambio);
};

$.fn.set_label_efectivo_recibido = function (efectivo_recibido) {
  $("#lbl_efectivo_recibido").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        parseFloat(efectivo_recibido).toFixed(2)
      )
  );
};

$.fn.cambiar_estilo_div_total_cambio = function () {
  $("#efectivo_recibido").css("background-color", "white");

  $("#div_total_cambio").attr("class", "danger");

  if (total_cambio.toFixed(0) >= 0)
    $("#div_total_cambio").attr("class", "success");
};

$.fn.activar_boton_guardar_factura = function () {
  $("#btn_guardar_factura").attr("disabled", "disabled");
  $("#btn_guardar_factura_electronica").attr("disabled", "disabled");
  $("#div_efectivo_recibido").show();
  $("#div_total_cambio").show();

  var valor_total_lineas_medios_recaudos = parseFloat(
    $("#total_valor_total").html().substring(1)
  );

  if (total_cambio.toFixed(0) >= 0 && valor_total_lineas_medios_recaudos == 0) {
    $("#btn_guardar_factura").removeAttr("disabled");
    $("#btn_guardar_factura_electronica").removeAttr("disabled");
    return true;
  }

  if ($("#forma_pago").val() == "credito") {
    $("#div_efectivo_recibido").hide();
    $("#div_total_cambio").hide();
    $("#btn_guardar_factura").removeAttr("disabled");
    $("#btn_guardar_factura_electronica").removeAttr("disabled");
    return true;
  }

  // Cuando se ingresan lineas de medios de recaudo el valor total debe ser exacto al de la factura.
  if (valor_total_lineas_medios_recaudos != 0) {
    var ajuste_al_peso = 0;

    var diferencia =
      parseFloat($("#valor_total_factura").val()) -
      (valor_total_lineas_medios_recaudos + ajuste_al_peso);

    if (Math.abs(diferencia) < 1) {
      $("#btn_guardar_factura").removeAttr("disabled");
      $("#btn_guardar_factura_electronica").removeAttr("disabled");
      $("#msj_medios_pago_diferentes_total_factura").hide();
    } else {
      $("#div_total_cambio").attr("class", "danger");
      $("#msj_medios_pago_diferentes_total_factura").show();
    }
  }
};

$.fn.set_valor_pendiente_ingresar_medios_recaudos = function () {
  var valor_total_factura = parseFloat($("#valor_total_factura").val());

  var valor_total_lineas_medios_recaudos = parseFloat(
    $("#total_valor_total").html().substring(1)
  );

  $("#lbl_vlr_pendiente_ingresar").html(
    "$ " + (valor_total_factura - valor_total_lineas_medios_recaudos).toFixed(2)
  );
};

$.fn.getCookie = function (cname) {
  var name = cname + "=";
  var ca = document.cookie.split(";");
  for (var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == " ") {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
};

$.fn.redondear_a_centena = function (numero, aproximacion_superior = false) {
  if (redondear_centena == 0) {
    return numero.toFixed(0);
  }

  var millones = 0;
  var millares = 0;
  var centenas = 0;

  var saldo1, saldo2, saldo3;

  if (numero > 999999.99999) {
    // se obtiene solo la parte entera
    millones = Math.trunc(numero / 1000000) * 1000000;
  }

  saldo1 = numero - millones;

  if (saldo1 > 999.99999) {
    // se obtiene solo la parte entera
    millares = Math.trunc(saldo1 / 1000) * 1000;
  }

  saldo2 = saldo1 - millares;

  if (saldo2 > 99.99999) {
    // se obtiene solo la parte entera
    //centenas = Math.trunc( saldo2 / 100 ) * 100;
    centenas = (saldo2 / 100).toFixed(0) * 100;
  }

  return millones + millares + centenas;
};

// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
// Debe ser complatible con las columnas de la tabla de ingreso de registros
$.fn.generar_string_celdas = function () {
  if (inv_producto_id === undefined) {
    return false;
  }

  var celdas = [];
  var num_celda = 0;

  celdas[num_celda] =
    '<td style="display: none;"><div class="inv_producto_id">' +
    inv_producto_id +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="precio_unitario">' +
    precio_unitario +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="base_impuesto">' +
    base_impuesto_unitario +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="tasa_impuesto">' +
    tasa_impuesto +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="valor_impuesto">' +
    valor_impuesto_unitario +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="base_impuesto_total">' +
    base_impuesto_unitario * cantidad +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="cantidad">' +
    cantidad +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="precio_total">' +
    precio_total +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="tasa_descuento">' +
    tasa_descuento +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="valor_total_descuento">' +
    valor_total_descuento +
    "</div></td>";

  num_celda++;

  celdas[num_celda] =
    '<td style="display: none;"><div class="lista_oculta_items_contorno_ids">' +
    $("#lista_oculta_items_contorno_ids").text() +
    "</div></td>";

  num_celda++;

  celdas[num_celda] = "<td> &nbsp; </td>";

  num_celda++;

  var descripcion_item = $("#inv_producto_id").val();
  if ($("#manejar_platillos_con_contorno").val() == 1) {
    descripcion_item = cambiar_descripcion_item_ingresado(descripcion_item);
  }

  celdas[num_celda] =
    '<td> <span style="background-color:#F7B2A3;">' +
    inv_producto_id +
    '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' +
    descripcion_item +
    " </div> </td>";

  num_celda++;

  celdas[num_celda] =
    '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' +
    cantidad +
    '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' +
    unidad_medida +
    "</div>)" +
    " </td>";

  num_celda++;

  celdas[num_celda] =
    '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar." id="elemento_modificar_precio_unitario">' +
    precio_unitario +
    "</div> </div> </td>";

  num_celda++;

  celdas[num_celda] =
    "<td>" +
    tasa_descuento +
    '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' +
    new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(0)) +
    "</div> ) </td>";

  num_celda++;

  celdas[num_celda] =
    '<td><div class="lbl_tasa_impuesto" style="display: inline;">' +
    tasa_impuesto +
    "</div></td>";

  num_celda++;

  var btn_borrar =
    "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
  celdas[num_celda] =
    '<td> <div class="lbl_precio_total" style="display: inline;">' +
    "$" +
    new Intl.NumberFormat("de-DE").format(precio_total.toFixed(0)) +
    " </div> </td> <td>" +
    btn_borrar +
    "</td>";

  var cantidad_celdas = celdas.length;
  var string_celdas = "";
  for (var i = 0; i < cantidad_celdas; i++) {
    string_celdas = string_celdas + celdas[i];
  }

  inv_producto_id = undefined; // para que no quede en memoria el código del producto

  return string_celdas;
};

/**
 * 
 * @returns boolean
 */
function set_cliente_default()
{
  if ( $("#lista_precios_id").val() != cliente_default.lista_precios_id && hay_productos > 0) {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "No puede cambiar a Consumidor Final. El cliente cliente seleccionado tiene una Lista de precios DIFERENTE para los productos ingresados. Debe retirar los productos ingresados.",
    });
    return false;
  }

  $("#cliente_id").val(cliente_default.id);
  $("#cliente_input").val(cliente_default.descripcion);
  $("#cliente_input").css("background-color", "transparent");

  $("#inv_bodega_id").val(cliente_default.inv_bodega_id);
  $("#forma_pago").val(forma_pago_default);
  $("#fecha_vencimiento").val(fecha_vencimiento_default);
  $("#lista_precios_id").val(cliente_default.lista_precios_id);
  $("#lista_descuentos_id").val(cliente_default.lista_descuentos_id);
  $("#liquida_impuestos").val(cliente_default.liquida_impuestos);
  $("#core_tercero_id").val(cliente_default.core_tercero_id);
  $("#zona_id").val(cliente_default.zona_id);
  $("#clase_cliente_id").val(cliente_default.clase_cliente_id);

  $("#cliente_descripcion_aux").val(cliente_default.descripcion);
  $("#numero_identificacion").val(cliente_default.numero_identificacion);
  $("#direccion1").val(cliente_default.direccion1);
  $("#telefono1").val(cliente_default.telefono1);
  
  set_lista_precios();
  $('.filtros').html('');
  $('#textinput_filter_item').val('');
}

/**
 * 
 */
function reset_campos_formulario() {
  $("#descripcion").val("");

  set_cliente_default()

  $("#lineas_registros").val(0); // Input que recoge el listado de productos

  $.fn.activar_boton_guardar_factura();

  if ($("#manejar_propinas").val() == 1) {
    $.fn.reset_propina();
  }

  if ($("#manejar_datafono").val() == 1) {
    $.fn.reset_datafono();
  }

  set_lista_precios();

  $('.filtros').html('');
  $('#textinput_filter_item').val('');
}

/**
 * 
 */
function ventana_imprimir() {
  ventana_factura = window.open(
    "",
    "Impresión de factura POS",
    "width=400,height=600,menubar=no"
  );

  ventana_factura.document.write($("#div_plantilla_factura").html());

  ventana_factura.print();
}

/**
 * 
 */
function mostrar_mensaje_item_agregado() {
  $("#popup_alerta").hide(200);
  $("#popup_alerta").css("background-color", "#00b998");
  $("#popup_alerta").css("color", "black");
  $("#popup_alerta").css("opacity", "revert");
  $("#popup_alerta").text("Producto agregado.");
  $("#popup_alerta").show(200);
}

// AL CARGAR EL DOCUMENTO
$(document).ready(function () {
  $("#btn_guardar").hide();

  $("#fecha").val(fecha);
  $("#fecha_vencimiento").val(fecha_vencimiento);

  agregar_la_linea_ini();

  $("#cliente_input").on("focus", function () {
    $(this).select();
  });

  $("#cliente_input").after('<div id="clientes_suggestions"> </div>');

  $("#forma_pago").on("change", function () {
    $.fn.activar_boton_guardar_factura();
  });

  // Al mostrar la ventana modal
  $("#recaudoModal").on("shown.bs.modal", function () {
    $("#form_registro").before(
      '<div id="div_pendiente_ingresar_medio_recaudo" style="color: red;">Pendiente por registrar: <span id="lbl_vlr_pendiente_ingresar">$ 0</span><div>'
    );
    $.fn.set_valor_pendiente_ingresar_medios_recaudos();
  });
  // Al OCULTAR la ventana modal
  $("#recaudoModal").on("hidden.bs.modal", function () {
    $("#div_pendiente_ingresar_medio_recaudo").remove();
  });

  $("#cliente_input").on("blur", function (event) {
    if ($("#cliente_input").val() == "") {
      $("#cliente_input").val($("#cliente_descripcion_aux").val());
    }
  });

  // Al ingresar código, descripción o código de barras del producto
  $("#cliente_input").on("keyup", function (event) {
    var codigo_tecla_presionada = event.which || event.keyCode;

    switch (codigo_tecla_presionada) {
      case 27: // 27 = ESC
        $("#clientes_suggestions").html("");
        $("#clientes_suggestions").hide();
        break;

      case 40: // Flecha hacia abajo
        var item_activo = $("a.list-group-item.active");
        item_activo
          .next()
          .attr("class", "list-group-item list-group-item-cliente active");
        item_activo.attr("class", "list-group-item list-group-item-cliente");
        $("#cliente_input").val(item_activo.next().html());
        break;

      case 38: // Flecha hacia arriba
        $(".flecha_mover:focus").prev().focus();
        var item_activo = $("a.list-group-item.active");
        item_activo
          .prev()
          .attr("class", "list-group-item list-group-item-cliente active");
        item_activo.attr("class", "list-group-item list-group-item-cliente");
        $("#cliente_input").val(item_activo.prev().html());
        break;

      case 13: // Al presionar Enter
        if ($(this).val() == "") {
          return false;
        }

        var item = $("a.list-group-item.active");

        if (item.attr("data-cliente_id") === undefined) {
          alert("El cliente ingresado no existe.");
          //reset_campos_formulario();
        } else {
          seleccionar_cliente(item);
        }
        break;

      default:
        // Manejo código de producto o nombre
        var campo_busqueda = "descripcion";
        if ($.isNumeric($(this).val())) {
          var campo_busqueda = "numero_identificacion";
        }

        // Si la longitud es menor a tres, todavía no busca
        if ($(this).val().length < 2) {
          return false;
        }

        var url = "../vtas_consultar_clientes";

        if ($("#action").val() == "edit") {
          var url = "../../vtas_consultar_clientes";
        }

        $.get(url, {
          texto_busqueda: $(this).val(),
          campo_busqueda: campo_busqueda,
        }).done(function (data) {
          // Se llena el DIV con las sugerencias que arooja la consulta
          $("#clientes_suggestions").show().html(data);
          $("a.list-group-item.active").focus();
        });
        break;
    }
  });

  $(".btn_vendedor").on("click", function (e) {
    e.preventDefault();

    $(".vendedor_activo").attr("class", "btn btn-default btn_vendedor");

    $(this).attr("class", "btn btn-default btn_vendedor vendedor_activo");

    $("#efectivo_recibido").select();

    $("#vendedor_id").val($(this).attr("data-vendedor_id"));
    $("#vendedor_id").attr(
      "data-vendedor_descripcion",
      $(this).attr("data-vendedor_descripcion")
    );
    $(document).prop(
      "title",
      $(this).attr("data-vendedor_descripcion").toUpperCase()
    );
  });
});
