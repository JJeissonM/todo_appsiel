// Variables de cada línea de ingresos de registros.
var producto_id,
  inv_producto_id,
  inv_bodega_id,
  inv_motivo_id,
  unidad_medida;

var ventana_factura;
var numero_linea = 0;


// Se debe llamar desde el DIV con ID total_valor_total
$.fn.actualizar_medio_recaudo = function () {
  var texto_total_recaudos = parseFloat(this.html().substring(1));

  calcular_total_cambio(texto_total_recaudos);

  $("#efectivo_recibido").val(texto_total_recaudos);
  $("#total_efectivo_recibido").val(texto_total_recaudos);

  set_label_efectivo_recibido(texto_total_recaudos);

  cambiar_estilo_div_total_cambio();

  activar_boton_guardar_factura();

  if (texto_total_recaudos == 0) {
    $("#efectivo_recibido").val("");
    $("#efectivo_recibido").removeAttr("readonly");
  } else {
    $("#efectivo_recibido").attr("readonly", "readonly");
  }

  set_valor_pendiente_ingresar_medios_recaudos();
};

//
function calcular_total_cambio(efectivo_recibido) {
  total_cambio =
    (redondear_a_centena(parseFloat($("#valor_total_factura").val())) -
      parseFloat(efectivo_recibido)) *
    -1;

  // Label
  $("#total_cambio").text(
    "$ " + new Intl.NumberFormat("de-DE").format(total_cambio.toFixed(0))
  );
  // Input hidden
  $("#valor_total_cambio").val(total_cambio);
};

function set_label_efectivo_recibido(efectivo_recibido) {
  $("#lbl_efectivo_recibido").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        parseFloat(efectivo_recibido).toFixed(2)
      )
  );
};

function cambiar_estilo_div_total_cambio() {
  $("#efectivo_recibido").css("background-color", "white");

  $("#div_total_cambio").attr("class", "danger");

  if (total_cambio.toFixed(0) >= 0)
    $("#div_total_cambio").attr("class", "success");
};

function set_valor_pendiente_ingresar_medios_recaudos() {
  var valor_total_factura = parseFloat($("#valor_total_factura").val());

  var valor_total_lineas_medios_recaudos = parseFloat(
    $("#total_valor_total").html().substring(1)
  );

  $("#lbl_vlr_pendiente_ingresar").html(
    "$ " + (valor_total_factura - valor_total_lineas_medios_recaudos).toFixed(2)
  );
};

function getCookie(cname) {
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

function redondear_a_centena(numero, aproximacion_superior = false) {
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
function generar_string_celdas() {
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
  //if ($("#manejar_platillos_con_contorno").val() == 1) {
  //  descripcion_item = cambiar_descripcion_item_ingresado(descripcion_item);
  //}

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
 */
function reset_campos_formulario() {
  $("#descripcion").val("");

  set_cliente_default()
  $('.filtros').html('');
  $('#textinput_filter_item').val('');

  $("#lineas_registros").val(0); // Input que recoge el listado de productos

  activar_boton_guardar_factura();

  if ($("#manejar_propinas").val() == 1) {
    reset_propina();
  }

  if ($("#manejar_datafono").val() == 1) {
    reset_datafono();
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
 * @returns boolean
 * Se llama desde el BOTON GUARDAR FACTURA
 */
function validar_producto_con_contorno()
{ 
  return true;
}

// AL CARGAR EL DOCUMENTO
$(document).ready(function () {
  $("#btn_guardar").hide();

  $("#fecha").val(fecha);
  $("#fecha_vencimiento").val(fecha_vencimiento);

  agregar_la_linea_ini();

  $("#btn_cargar_plano").on("click", function (event) {
    event.preventDefault();

    if (!validar_requeridos()) {
      return false;
    }

    $("#div_spin").show();
    $("#div_cargando").show();

    var form = $("#form_archivo_plano");
    var url = form.attr("action");
    var datos = new FormData(document.getElementById("form_archivo_plano"));

    $.ajax({
      url: url,
      type: "post",
      dataType: "html",
      data: datos,
      cache: false,
      contentType: false,
      processData: false,
    }).done(function (respuesta) {
      $("#div_cargando").hide();
      $("#div_spin").hide();

      $("#ingreso_registros").find("tbody:last").prepend(respuesta);
      calcular_totales();

      $("#btn_nuevo").show();

      hay_productos = $("#ingreso_registros tr").length - 2;
      $("#numero_lineas").html(hay_productos);
      set_cantidades_ingresadas();

      $("#inv_producto_id").focus();
    });
  });
});
