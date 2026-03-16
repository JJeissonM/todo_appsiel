var hay_productos = 0;
var url_raiz, redondear_centena, numero_linea;
var inv_producto_id, doc_encabezado, puede_continuar, vlr_efectivo_recibido;
var locked = false;
var pos_save_attempted = false;
var guardar_factura_watchdog = null;
var pos_tab_heartbeat_timer = null;
var pos_tab_runtime_id = "";

$("#btn_nuevo").hide();
$("#btn_cancelar").hide();
$("#btnPaula").hide();

$("#teso_motivo_id").val(1);
$("#teso_caja_id").val($("#caja_pdv_default_id").val());

$("#inv_producto_id").select();

function ejecutar_acciones_con_item_sugerencia(
  item_sugerencia,
  obj_text_input
) {
  $(".text_input_sugerencias").select();
}

/**
 * 
 */
function validar_fecha_diferente() {
  if ($("#fecha").val() != get_fecha_hoy()) {
    $("#msj_fecha_diferente").show();
  } else {
    $("#msj_fecha_diferente").hide();
  }
}

/**
 * 
 */
document.getElementById("btn_pruebas").addEventListener("click", function(event){
  event.preventDefault()
});
document.addEventListener("DOMContentLoaded", function(){
  inicializar_contexto_pos_multitab();
});
window.addEventListener("focus", function(){
  if ($("#form_create").length > 0) {
    activar_boton_guardar_factura();
    if (!locked) {
      reset_estado_boton_guardar_factura();
    }
  }
});


function crear_identificador_unico_pos()
{
  return uniqid() + "_" + Date.now().toString(36) + "_" + Math.floor(Math.random() * 1000000).toString(36);
}

function get_pos_tab_storage_prefix()
{
  return "pos_tab_context_" + ( $("#pdv_id").val() || "0" ) + "_";
}

function iniciar_heartbeat_tab_pos(tab_id)
{
  try {
    var key = get_pos_tab_storage_prefix() + "active_" + tab_id;
    var payload = JSON.stringify({ heartbeat: Date.now(), runtime: pos_tab_runtime_id });
    localStorage.setItem(key, payload);

    if (pos_tab_heartbeat_timer !== null) {
      clearInterval(pos_tab_heartbeat_timer);
    }

    pos_tab_heartbeat_timer = setInterval(function(){
      try {
        localStorage.setItem(key, JSON.stringify({ heartbeat: Date.now(), runtime: pos_tab_runtime_id }));
      } catch (e) {
        console.error(e);
      }
    }, 5000);

    window.addEventListener("beforeunload", function(){
      try {
        var current = localStorage.getItem(key);
        if (!current) { return; }

        var data = JSON.parse(current);
        if (data && data.runtime === pos_tab_runtime_id) {
          localStorage.removeItem(key);
        }
      } catch (e) {
        console.error(e);
      }
    });
  } catch (e) {
    console.error(e);
  }
}

function inicializar_contexto_pos_multitab()
{
  if ($("#form_create").length === 0) {
    return;
  }

  pos_tab_runtime_id = crear_identificador_unico_pos();

  var prefix = get_pos_tab_storage_prefix();
  var tab_key = prefix + "tab_id";
  var draft_key = prefix + "draft_id";

  var tab_id = sessionStorage.getItem(tab_key);
  var draft_id = sessionStorage.getItem(draft_key);

  var generar_nuevo_contexto = false;
  if (!tab_id || !draft_id) {
    generar_nuevo_contexto = true;
  } else {
    try {
      var active_key = prefix + "active_" + tab_id;
      var active_data_raw = localStorage.getItem(active_key);
      if (active_data_raw) {
        var active_data = JSON.parse(active_data_raw);
        if (active_data && (Date.now() - parseInt(active_data.heartbeat || 0, 10) < 15000)) {
          generar_nuevo_contexto = true;
        }
      }
    } catch (e) {
      console.error(e);
    }
  }

  if (generar_nuevo_contexto) {
    tab_id = crear_identificador_unico_pos();
    draft_id = crear_identificador_unico_pos();
    sessionStorage.setItem(tab_key, tab_id);
    sessionStorage.setItem(draft_key, draft_id);
  }

  if ($("#tab_instance_id").length > 0) {
    $("#tab_instance_id").val(tab_id);
  }
  if ($("#draft_id").length > 0) {
    $("#draft_id").val(draft_id);
  }

  update_uniqid();
  update_request_id();
  iniciar_heartbeat_tab_pos(tab_id);
}

function update_request_id()
{
  if ($("#request_id").length > 0) {
    $("#request_id").val(crear_identificador_unico_pos());
  }
}

function iniciar_watchdog_guardado(ms)
{
  limpiar_watchdog_guardado();

  guardar_factura_watchdog = setTimeout(function(){
    if ($("#btn_guardando").length > 0) {
      reset_estado_boton_guardar_factura();
      Swal.fire({
        icon: "warning",
        title: "Guardado demorado",
        text: "La operacion tardo mas de lo esperado. Verifica en historial si la factura fue creada antes de reintentar."
      });
    }
  }, ms);
}

function limpiar_watchdog_guardado()
{
  if (guardar_factura_watchdog !== null) {
    clearTimeout(guardar_factura_watchdog);
    guardar_factura_watchdog = null;
  }
}

/**
 * 
 * @param {*} value 
 * @returns texto_motivo
 */
function get_text_from_select_for_value( value )
{
  let texto_motivo = '';
  let a = document.getElementById("teso_motivo_id");
  for (let i = 0; i < a.length; i++) {
      let option = a.options[i];
      if (option.value == value) {
        texto_motivo = option.text;
      }
  }

  return texto_motivo;
}

/**
 * 
 * @returns json_table2
 */
function get_json_registros_medios_recaudo() {

  var table2 = $("#ingreso_registros_medios_recaudo").tableToJSON();

  var json_table2 = "";
  if (table2.length == 1) {
    // Solo tiene la linea de totales, se ingresan datos por defecto
     
    let teso_motivo_default_id = $( "#teso_motivo_default_id" ).val();
    let texto_motivo = get_text_from_select_for_value( teso_motivo_default_id );

    let total_factura = parseFloat( $("#valor_total_factura").val() ) + parseFloat( $("#valor_ajuste_al_peso").val() );
    
    json_table2 =
      '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"' + teso_motivo_default_id + '-' + texto_motivo + '","teso_caja_id":"' +
      $("#caja_pdv_default_id").val() +
      "-" +
      $("#caja_pdv_default_label").val() +
      '","teso_cuenta_bancaria_id":"0-","valor":"$' + total_factura +
      '"}]';
  } else {
    json_table2 = "[";
    var el_primero = true;
    table2.forEach((element) => {
      if (element.teso_caja_id != "") {
        if (el_primero) {
          json_table2 += JSON.stringify(element);
          el_primero = false;
        } else {
          json_table2 += "," + JSON.stringify(element);
        }
      }
    });
    json_table2 += "]";
  }
  return json_table2;
}

function add_impuesto_id_to_table_lines(table) {
  return table.map(function (line, index) {
    var invId = line.inv_producto_id;

    if (invId === undefined || invId === "") {
      invId = $("#ingreso_registros tbody tr")
        .eq(index)
        .find(".inv_producto_id")
        .text();
    }

    var producto = productos.find(function (item) {
      return item.id === parseInt(invId, 10);
    });

    line.impuesto_id = producto ? producto.impuesto_id : 0;

    return line;
  });
}
/**
 * 
 * @param {*} con_medios_recaudos 
 */
function llenar_tabla_productos_facturados( con_medios_recaudos = true ) 
{
  var linea_factura, linea_factura2;
  var lbl_total_factura = 0;
  var lbl_base_impuesto_total = 0;
  var lbl_valor_impuesto = 0;
  var valor_total_bolsas = 0;

  var cantidad_total_productos = 0;

  $(".linea_registro").each(function () {

    linea_factura =
      '<tr> <td style="padding: 4px;"> ' +
      $(this).find('.lbl_producto_descripcion').text() +
      ' (' +
      $(this).find('.lbl_producto_unidad_medida').text() +
      ') </td> <td style="text-align: center;"> ' +
      $(this).find('.cantidad').text() +
      '<br> ($' +
      new Intl.NumberFormat("de-DE").format(
          parseFloat( $(this).find('.precio_unitario').text()).toFixed(0)
        ) +
      ') </td> <td> ' +
      $(this).find('.lbl_tasa_impuesto').text() +
      '</td> <td> ' +
      $(this).find('.lbl_precio_total').text() +
      '  </td></tr>';

    // Para formato impresora 58mm
    if (
      $("#plantilla_factura_pos_default").val() == "plantilla_impresora_58mm"
    ) {
      linea_factura =
        '<tr> <td style="width: 100px;"> ' +
        $(this).find(".lbl_producto_descripcion").text() +
        " x" +
        $(this).find(".lbl_producto_unidad_medida").text() +
        " </td> <td> " +
        $(this).find(".cantidad").text() +
        " ($" +
        $(this).find(".precio_unitario").text() +
        ") </td> <td> " +
        $(this).find(".lbl_precio_total").text() +
        "  </td></tr>";

      // WARNING!!! Esto esta manual, puede estar errado
      lbl_base_impuesto_total += parseFloat(
        $(this).find(".base_impuesto_total").text()
      );
      lbl_valor_impuesto += parseFloat($(this).find(".valor_impuesto").text());
    }

    if (parseFloat($(this).find(".valor_total_descuento").text()) != 0) {
      linea_factura +=
        '<tr> <td colspan="2" style="text-align: right;">Dcto.</td> <td colspan="2"> ( -$' +
        new Intl.NumberFormat("de-DE").format(
          parseFloat($(this).find(".valor_total_descuento").text()).toFixed(0)
        ) +
        " ) </td> </tr>";
    }

    $("#tabla_productos_facturados").find("tbody:last").append(linea_factura);

    // Para El formato con Remisión
    linea_factura2 =
      '<tr> <td style="border-bottom:solid 1px !important;"> ' +
      $(this).find(".lbl_producto_descripcion").text() +
      " </td> <td> " +
      $(this).find(".cantidad").text() +
      " " +
      $(this).find(".lbl_producto_unidad_medida").text() +
      "  </td></tr>";
    $("#tabla_productos_facturados2").find("tbody:last").append(linea_factura2);

    lbl_total_factura += parseFloat($(this).find(".precio_total").text());
    
    // precio_bolsa es distinto a cero cuando esta habilitada la facturación de bolsas 
    if ( $("#precio_bolsa").val() != 0 && item_is_in_group( $(this).find('.inv_producto_id').text(), 'categoria_id_facturacion_bolsa' ) ) {
  
      valor_total_bolsas += parseFloat( $("#precio_bolsa").val() );

      lbl_total_factura += parseFloat( $("#precio_bolsa").val() );

    }

    cantidad_total_productos++;
  }); // Fin por cada línea de productos

  var total_factura_redondeado = redondear_a_centena(lbl_total_factura);

  $(".lbl_total_factura").text(
    "$ " + new Intl.NumberFormat("de-DE").format(total_factura_redondeado)
  );

  var valor_propina = 0.0;
  var valor_datafono = 0.0;
  if ($("#manejar_propinas").val() == 1) {
    valor_propina = parseFloat($("#valor_propina").val());
    $(".lbl_total_propina").text(
      "$ " + new Intl.NumberFormat("de-DE").format(valor_propina)
    );
  }

  if ($("#manejar_datafono").val() == 1) {
    valor_datafono = parseFloat($("#valor_datafono").val());
    $(".lbl_total_datafono").text(
      "$ " + new Intl.NumberFormat("de-DE").format(valor_datafono)
    );
  }

  $(".lbl_total_factura_mas_recargos").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        parseFloat(total_factura_redondeado) + valor_propina + valor_datafono
      )
  );

  $(".lbl_base_impuesto_total").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        redondear_a_centena(lbl_base_impuesto_total)
      )
  );

  $(".lbl_valor_impuesto").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        redondear_a_centena(lbl_valor_impuesto)
      )
  );

  $(".lbl_ajuste_al_peso").text(
    "$ " + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso)
  );


  $(".lbl_valor_total_bolsas").text(
    "$ " + new Intl.NumberFormat("de-DE").format(valor_total_bolsas)
  );
  
  var efectivo_recibido = 0;
  if ( con_medios_recaudos ) {
    efectivo_recibido = parseFloat($("#efectivo_recibido").val());
  }  
  $(".lbl_total_recibido").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format( efectivo_recibido )
  );


  var lbl_total_cambio = 0;
  if ( con_medios_recaudos ) {
    lbl_total_cambio = total_cambio;
  }

  $(".lbl_total_cambio").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        redondear_a_centena( lbl_total_cambio )
      )
  );

  $(".lbl_condicion_pago").text($("#forma_pago").val());
  $(".lbl_fecha_vencimiento").text($("#fecha_vencimiento").val());

  $("#cantidad_total_productos").text(cantidad_total_productos);

  $("#tr_fecha_vencimiento").hide();
  $("#tr_total_recibido").show();
  $("#tr_total_cambio").show();
  if ($("#forma_pago").val() == "credito") {
    $("#tr_fecha_vencimiento").show();
    $("#tr_total_recibido").hide();
    $("#tr_total_cambio").hide();
  }

  $("#lbl_fecha").text($("#fecha").val() );
  var d = new Date();
  $("#lbl_hora").text(
    addZero(get_hora(d.getHours())) +
      ":" +
      addZero(d.getMinutes()) +
      " " +
      get_horario(d.getHours())
  );

  $(".lbl_cliente_descripcion").text($("#cliente_descripcion_aux").val());
  $(".lbl_cliente_nit").text($("#numero_identificacion").val());
  $(".lbl_cliente_direccion").text($("#direccion1").val());
  $(".lbl_cliente_telefono").text($("#telefono1").val());

  $(".lbl_atendido_por").text(
    $("#vendedor_id").attr("data-vendedor_descripcion")
  );

  $(".lbl_descripcion_doc_encabezado").text($("#descripcion").val());

  llenar_resumen_impuestos();

  if ( con_medios_recaudos ) {
    llenar_resumen_medios_recaudo();
  }
}

/**
 * 
 */
function llenar_resumen_impuestos() {

  $("#div_resumen_impuestos").show();
  let array_tasas = {};

  $(".linea_registro").each(function () {

    var linea = {};
    linea.cantidad = $(this).find(".cantidad").text();
    linea.base_impuesto = $(this).find(".base_impuesto_total").text();
    linea.valor_impuesto = $(this).find(".valor_impuesto").text();
    linea.tasa_impuesto = $(this).find(".tasa_impuesto").text();
    linea.precio_total = $(this).find(".precio_total").text();

    // Si la tasa no está en el array, se agregan sus valores por primera vez
    if (typeof array_tasas[linea.tasa_impuesto] === 'undefined') {
        // Clasificar el impuesto
        array_tasas[linea.tasa_impuesto] = {};
        array_tasas[linea.tasa_impuesto]['tipo'] = 'IVA=' + linea.tasa_impuesto + '%';
        if (parseFloat(linea.tasa_impuesto) === 0) {
            array_tasas[linea.tasa_impuesto]['tipo'] = 'EX=0%';
        }
        if (parseFloat(linea.tasa_impuesto) === 8) {
            array_tasas[linea.tasa_impuesto]['tipo'] = 'INC=' + linea.tasa_impuesto + '%';
        }
        // Guardar la tasa en el array
        array_tasas[linea.tasa_impuesto]['tasa'] = linea.tasa_impuesto;

        // Guardar el primer valor del impuesto y base en el array
        array_tasas[linea.tasa_impuesto]['precio_total'] = parseFloat(linea.precio_total);
        array_tasas[linea.tasa_impuesto]['base_impuesto'] = parseFloat(linea.base_impuesto);
        array_tasas[linea.tasa_impuesto]['valor_impuesto'] = parseFloat(linea.valor_impuesto) * parseFloat(linea.cantidad);

    } else {
        // Si ya está la tasa creada en el array
        // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
        array_tasas[linea.tasa_impuesto]['precio_total'] += parseFloat(linea.precio_total);
        array_tasas[linea.tasa_impuesto]['base_impuesto'] += parseFloat(linea.base_impuesto);
        array_tasas[linea.tasa_impuesto]['valor_impuesto'] += parseFloat(linea.valor_impuesto) * parseFloat(linea.cantidad);
    }
  });

  $.each(array_tasas,function (index, value) {

    $("#tabla_resumen_impuestos")
      .find("tbody:last")
      .append(
        "<tr><td>" + value.tipo + "</td> <td style='text-align: right'>$ " + new Intl.NumberFormat("de-DE").format( value.precio_total.toFixed(0) ) + "</td> <td style='text-align: right'>$ " + new Intl.NumberFormat("de-DE").format( value.base_impuesto.toFixed(0) ) + "</td> <td style='text-align: right'>$ "+ new Intl.NumberFormat("de-DE").format( value.valor_impuesto.toFixed(0) ) + "</td></tr>"
      );
  });
  
}

/**
 * 
 * @returns 
 */
function llenar_resumen_medios_recaudo() {
  if ($("#forma_pago").val() == "credito") {
    $("#div_resumen_medios_pago").hide();
    return 0;
  }

  $("#div_resumen_medios_pago").show();

  var valor_total_lineas_medios_recaudos = parseFloat(
    $("#total_valor_total").html().substring(1)
  );

  if (valor_total_lineas_medios_recaudos == 0) {
    var lbl_medio_pago = "Efectivo";

    $("#teso_caja_id").val($("#caja_pdv_default_id").val());

    var lbl_caja_banco = $("#teso_caja_id option:selected").text();

    var lbl_valor_medio_pago =
      $("#total_efectivo_recibido").val() - $("#valor_total_cambio").val();

    $("#tabla_resumen_medios_pago")
      .find("tbody:last")
      .append(
        "<tr><td>" +
          lbl_medio_pago +
          "</td><td>" +
          lbl_caja_banco +
          "</td><td style='text-align: right'>$ " + new Intl.NumberFormat("de-DE").format(lbl_valor_medio_pago ) +
          "</td></tr>"
      );
  } else {
    $("#ingreso_registros_medios_recaudo > tbody > tr").each(function () {
      var array_celdas = $(this).find("td");
      var lbl_medio_pago = array_celdas.eq(0).find("span").eq(1).text();
      var lbl_caja_banco =
        array_celdas.eq(2).find("span").eq(1).text() +
        " - " +
        array_celdas.eq(3).find("span").eq(1).text();
      var lbl_valor_medio_pago = array_celdas.eq(4).text().substring(1);
      
      $("#tabla_resumen_medios_pago")
        .find("tbody:last")
        .append(
          "<tr><td>" +
            lbl_medio_pago +
            "</td><td>" +
            lbl_caja_banco +
            "</td><td style='text-align: right'>$ " +
            new Intl.NumberFormat("de-DE").format( lbl_valor_medio_pago ) +
            "</td></tr>"
        );
    });
  }
}

/**
 */
function resetear_ventana() {
  $("#tabla_productos_facturados").find("tbody").html("");
  $("#tabla_productos_facturados2").find("tbody").html("");
  $("#tabla_resumen_medios_pago").find("tbody").html("");

  reset_tabla_ingreso_items();
  reset_resumen_de_totales();
  reset_linea_ingreso_default();
  reset_tabla_ingreso_medios_pago();
  reset_efectivo_recibido();
  reset_campos_formulario();

  $("#btn_cancelar").show();
  $("#btn_cancelar_pedido").hide();

  $("#lbl_ajuste_al_peso").text("$ ");
  $("#valor_ajuste_al_peso").val(0);

  $("#lbl_valor_total_bolsas").text("$ ");
  $("#valor_valor_total_bolsas").val(0);

  $("#msj_ventana_impresion_abierta").hide();

  // Vendedor default
  if ($("#pedido_id").val() != 0) {
    $("#vendedor_id").val(cliente_default.vendedor_id);
    $("#vendedor_id").attr(
      "data-vendedor_descripcion",
      cliente_default.vendedor_descripcion
    );
    $(".vendedor_activo").attr("class", "btn btn-default btn_vendedor");
    $("button[data-vendedor_id='" + cliente_default.vendedor_id + "']").attr(
      "class",
      "btn btn-default btn_vendedor vendedor_activo"
    );
    $(document).prop("title", cliente_default.vendedor_descripcion);
  }

  if ($("#manejar_propinas").val() == 1) {
    $("#valor_sub_total_factura").val(0);
    $("#lbl_sub_total_factura").text("$ 0");

    reset_propina();
  }

  if ($("#manejar_datafono").val() == 1) {
    $("#valor_sub_total_factura").val(0);
    $("#lbl_sub_total_factura").text("$ 0");

    reset_datafono();
  }
}

/**
 * 
 */
function reset_tabla_ingreso_items() {
  $(".linea_registro").each(function () {
    $(this).remove();
  });
  hay_productos = 0;
  numero_lineas = 0;
  $("#numero_lineas").text("0");
  set_cantidades_ingresadas();
}

/**
 * 
 */
function reset_resumen_de_totales() {
  // reset totales
  $("#total_cantidad").text("0");

  // Subtotal (Sumatoria de base_impuestos por cantidad)
  $("#subtotal").text("$ 0");

  $("#descuento").text("$ 0");

  // Total impuestos (Sumatoria de valor_impuesto por cantidad)
  $("#total_impuestos").text("$ 0");

  // Total factura  (Sumatoria de precio_total)
  $("#total_factura").text("$ 0");
  $("#valor_total_factura").val(0);

  $("#div_total_cambio").attr("class", "default");
}

/**
 * 
 */
function enfocar_tab_totales()
{
    $('#header_tab3').removeAttr('class');
    $('#header_tab2').removeAttr('class');
    $('#header_tab1').attr('class','active');
    
    $('#tab3').attr('class','tab-pane fade');
    $('#tab2').attr('class','tab-pane fade');
    $('#tab1').attr('class','tab-pane fade active in');
}

/**
 * 
 */
function reset_tabla_ingreso_medios_pago() {
  $("#ingreso_registros_medios_recaudo").find("tbody").html("");

  $("#tabla_resumen_medios_pago").find("tbody").html("");
  $("#tabla_resumen_impuestos").find("tbody").html("");

  // reset totales
  $("#total_valor_total").text("$0.00");
}

/**
 * 
 */
function reset_linea_ingreso_default() {
  $("#efectivo_recibido").val("");

  $("#inv_producto_id").val("");
  $("#cantidad").val("");
  $("#precio_unitario").val("");
  $("#tasa_descuento").val("");
  $("#tasa_impuesto").val("");
  $("#precio_total").val("");

  $("#popup_alerta").hide();

  inv_producto_id = 0;
  precio_total = 0;
  costo_total = 0;
  base_impuesto_total = 0;
  valor_impuesto_total = 0;
  tasa_impuesto = 0;
  tasa_descuento = 0;
  valor_total_descuento = 0;
  cantidad = 0;
  costo_unitario = 0;
  precio_unitario = 0;
  base_impuesto_unitario = 0;
  valor_impuesto_unitario = 0;
  valor_unitario_descuento = 0;
}

/**
 * 
 */
function reset_efectivo_recibido() {
  $("#efectivo_recibido").val("");
  $("#total_efectivo_recibido").val(0);
  $("#lbl_efectivo_recibido").text("$ 0");
  $("#total_cambio").text("$ 0");
  total_cambio = 0;
  $("#btn_guardar_factura").attr("disabled", "disabled");
  $("#btn_guardar_factura_electronica").attr("disabled", "disabled");
}

function addZero(i) {
  if (i < 10) {
    i = "0" + i;
  }
  return i;
}

function get_hora(i) {
  if (i > 12) {
    i -= 12;
  }
  return i;
}

function get_horario(i) {
  if (i > 12) {
    return "pm";
  }
  return "am";
}

// Al presionar Enter en el ingreso del producto sea por ID, Referencia o Codigo de barras
function agregar_linea_producto_ingresado(
  producto,
  barcode,
  barcode_precio_unitario,
  campo_busqueda
) {
  tasa_impuesto = producto.tasa_impuesto;
  inv_producto_id = producto.id;
  unidad_medida = producto.unidad_medida1;
  costo_unitario = producto.costo_promedio;

  $("#inv_producto_id").val(producto.descripcion);

  $("#existencia_actual").html(
    "Stock: " + producto.existencia_actual.toFixed(2)
  );
  //$('#existencia_actual').show();

  $("#precio_unitario").val(get_precio(producto.id));
  $("#tasa_descuento").val(get_descuento(producto.id));

  if (campo_busqueda == "id" || campo_busqueda == "referencia") 
  {
    $("#cantidad").select();
  } else {
    // Por código de barras, se agrega la línea con un unidad de producto
    $("#cantidad").val(1);
    cantidad = 1;

    // Para balazas Dibal, obtener la cantidad del mismo codigo de barras
    if ( $("#forma_lectura_codigo_barras").val() == "codigo_cantidad" && barcode.substr(0, 1) == 0 )
    {
      $("#cantidad").val(get_quantity_from_barcode(barcode));
      cantidad = parseFloat(get_quantity_from_barcode(barcode));
      if (barcode_precio_unitario != "") 
      {
        $("#precio_unitario").val(barcode_precio_unitario);
      }
    }

    calcular_valor_descuento();
    calcular_impuestos();    

    agregar_nueva_linea(); // Cuando es por Codigo de barras
    $("#inv_producto_id").focus();
  }
}

/**
 * 
 * @param {*} barcode 
 * @returns 
 */
function get_item_id_from_barcode(barcode) {
  return parseInt(barcode.substr(0, 6));
}

/**
 * 
 * @param {*} barcode 
 * @returns 
 */
function get_quantity_from_barcode(barcode) {
  return barcode.substr(6, 2) + "." + barcode.substr(8, 3);
}

/**
 * 
 */
function enable_boton_guardar_factura()
{
  $("#btn_guardar_factura").removeAttr("disabled");
  $("#btn_guardar_factura_electronica").removeAttr("disabled");
}

/**
 * 
 */
function disable_boton_guardar_factura()
{
  $("#btn_guardar_factura").attr("disabled", "disabled");
  $("#btn_guardar_factura_electronica").attr("disabled", "disabled");
}

function activar_estado_boton_guardando_factura()
{
  if ($("#btn_guardar_factura").length === 0) {
    return;
  }

  $("#btn_guardar_factura").html('<i class="fa fa-spinner fa-spin"></i> Guardando');
  $("#btn_guardar_factura").attr("disabled", "disabled");
  $("#btn_guardar_factura").attr("id", "btn_guardando");
}

function reset_estado_boton_guardar_factura()
{
  if ($("#btn_guardando").length > 0) {
    $("#btn_guardando").html('<i class="fa fa-check"></i> Guardar factura');
    $("#btn_guardando").attr("id", "btn_guardar_factura");
  }

  $("#btn_guardar_factura").removeAttr("disabled");
  limpiar_watchdog_guardado();
  locked = false;
}

/**
 * 
 * @returns 
 */
function activar_boton_guardar_factura()
{
  disable_boton_guardar_factura();
  $("#div_efectivo_recibido").show();
  $("#div_total_cambio").show();

  var valor_total_factura = redondear_a_centena( parseFloat( $("#valor_total_factura").val() ) );

  if ( valor_total_factura == 0 && hay_productos == 0 ) {
    return false;
  }

  if ($("#forma_pago").val() == "credito") {
    $("#div_efectivo_recibido").hide();
    $("#div_total_cambio").hide();
    enable_boton_guardar_factura();
    return true;
  }

  // total_valor_total es el total de los Medios de Pago
  var valor_total_lineas_medios_recaudos = parseFloat( $("#total_valor_total").html().substring(1) );

  vlr_efectivo_recibido = 0;
  if ( validar_input_numerico($("#efectivo_recibido")) ) {
    vlr_efectivo_recibido = parseFloat( $("#efectivo_recibido").val() );
  }
  
  // No se ingresaron lineas de medios de Pago
  if ( valor_total_lineas_medios_recaudos == 0 && vlr_efectivo_recibido >= valor_total_factura ) {
    enable_boton_guardar_factura();
    return true;
  }

  // Cuando se ingresan lineas de medios de recaudo el valor total debe ser exacto al de la factura.
  var ajuste_al_peso = 0;

  var diferencia = valor_total_factura - (valor_total_lineas_medios_recaudos + ajuste_al_peso);

  if (Math.abs(diferencia) < 1) {
    $("#btn_guardar_factura").removeAttr("disabled");
    $("#btn_guardar_factura_electronica").removeAttr("disabled");
    $("#msj_medios_pago_diferentes_total_factura").hide();
  } else {
    $("#div_total_cambio").attr("class", "danger");
    $("#msj_medios_pago_diferentes_total_factura").show();
  }
}

/**
 * 
 * @returns 
 */
function agregar_nueva_linea() 
{

  if (!calcular_precio_total()) {

    $("#popup_alerta").show();
    $("#popup_alerta").css("background-color", "red");
    $("#popup_alerta").text("Error en precio total. Por favor verifique");
    return false;
  }

  agregar_la_linea();
}

/**
 * 
 * @param {*} vlr_efectivo_recibido 
 */
function set_datos_efectivo_recibido( vlr_efectivo_recibido )
{
  $("#total_efectivo_recibido").val( vlr_efectivo_recibido );

  set_label_efectivo_recibido( vlr_efectivo_recibido );

  calcular_total_cambio( vlr_efectivo_recibido );

  activar_boton_guardar_factura();

  cambiar_estilo_div_total_cambio();
}

/**
 * 
 */
$(document).ready(function () {

  $("#btn_guardar").hide();
  
  if ($("#action").val() != "create") {
    reset_efectivo_recibido();
    $("#btn_nuevo").show();
  }

  if ($("#action").val() != "edit") {
    $("#fecha").val(get_fecha_hoy());
  }

  validar_fecha_diferente();

  $('#total_valor_total').actualizar_medio_recaudo();

  $(document).prop('title', $('#vendedor_id').attr('data-vendedor_descripcion').toUpperCase() );

  $("#forma_pago").on("change", function () {
      activar_boton_guardar_factura();
  });

  $("#fecha").on("change", function () {
    validar_fecha_diferente();
  });

  // Al ingresar código, descripción o código de barras del producto
  $("#inv_producto_id").on("keyup", function (event) {
    $("[data-toggle='tooltip']").tooltip("hide");
    $("#popup_alerta").hide();

    var codigo_tecla_presionada = event.which || event.keyCode;

    switch (codigo_tecla_presionada) {
      case 113: // 113 = F2
        $("#textinput_filter_item").select();

        break;

      case 27: // 27 = ESC
        $("#efectivo_recibido").val("");
        $("#efectivo_recibido").select();
        $("#efectivo_recibido").css("background-color", "white");

        break;

      case 13: // Al presionar Enter
        if ($(this).val() == "") {
          return false;
        }

        // Si la longitud del codigo ingresado es mayor que 5 (numero arbitrario)
        // se supone que es un código de barras
        var campo_busqueda = "";
        if ($(this).val().length > 5) {
          var barcode = $(this).val();
          var barcode_precio_unitario = $("#precio_unitario").val();

          if ($("#forma_lectura_codigo_barras").val() == "codigo_cantidad") {
            var el_item_id = get_item_id_from_barcode(barcode);
            var producto = productos.find(
              (item) => item.id === parseInt(el_item_id)
            );
          } else {
            var producto = productos.find(
              (item) => item.codigo_barras === $(this).val()
            );
          }

          campo_busqueda = "codigo_barras";
        } else {
          var producto = productos.find(
            (item) => item.id === parseInt($(this).val())
          );
          campo_busqueda = "id";
        }

        // Una segunda busqueda por Código de barras
        if (
          producto === undefined &&
          $("#forma_lectura_codigo_barras").val() == "codigo_cantidad"
        ) {
          var producto = productos.find(
            (item) => item.codigo_barras === $(this).val()
          );
        }

        if (producto === undefined) {
          var producto = productos.find(
            (item) => item.referencia === $(this).val()
          );
          campo_busqueda = "referencia";
        }

        if (producto !== undefined) {
          agregar_linea_producto_ingresado(
            producto,
            barcode,
            barcode_precio_unitario,
            campo_busqueda
          );
        } else {
          $("#popup_alerta").show();
          $("#popup_alerta").css("background-color", "red");
          $("#popup_alerta").text("Producto no encontrado.");
          $(this).select();
        }
        break;
      default:
        break;
    }
  });

  /**
   * EFECTIVO RECIBIDO
   */
  $("#efectivo_recibido").on("keyup", function (event) {
    $("#popup_alerta").hide();
    var codigo_tecla_presionada = event.which || event.keyCode;

    if (codigo_tecla_presionada == 27) { // 27:ESC
      $("#inv_producto_id").focus();
      return false;
    }

    if (codigo_tecla_presionada == 113) { // 113: F2      
      $("#textinput_filter_item").select();
      return false;
    }

    if ($("#valor_total_factura").val() <= 0) {
      
      set_datos_efectivo_recibido( 0 )
      $("#efectivo_recibido").css("background-color", "white");

      return false;
    }

    if ( !validar_input_numerico($(this)) ) {
      
      set_datos_efectivo_recibido( 0 )

      return false;
    }

    switch (codigo_tecla_presionada) {
      case 13: // Al presionar Enter
        if (total_cambio.toFixed(0) >= 0) {
          $("#btn_guardar_factura").focus();

          if ($("#ocultar_boton_guardar_factura_pos").val() == 1) {
            $("#header_tab1").removeAttr("class");
            $("#header_tab3").attr("class", "active");

            $("#tab1").attr("class", "tab-pane fade");
            $("#tab3").attr("class", "tab-pane fade active in");

            $("#btn_guardar_factura_electronica").focus();
          }
        } else {
          return false;
        }

        break;

      default:

        set_datos_efectivo_recibido( $(this).val() )

        break;
    }
  });
  
  $("#efectivo_recibido").on("focus", function (event) {
    $("#efectivo_recibido").css("background-color", "white");
  });

  /**
   * 
   */
  $("#cantidad").keyup(function (event) {
    var codigo_tecla_presionada = event.which || event.keyCode;

    if (codigo_tecla_presionada == 13 && $(this).val() == "") {
      $("#precio_unitario").select();
      return false;
    }

    if (validar_input_numerico($(this)) && $(this).val() > 0) {
      cantidad = parseFloat($(this).val());

      if (codigo_tecla_presionada == 13) 
      {
        // ENTER
        agregar_nueva_linea();
        $("#inv_producto_id").focus();
        return true;
      }

      if ($(this).val() != "") {
        calcular_valor_descuento();
        calcular_impuestos();
        calcular_precio_total();
      }
    } else {
      return false;
    }
  });

  // Al modificar el precio de venta
  $("#precio_unitario").keyup(function (event) {
    var codigo_tecla_presionada = event.which || event.keyCode;

    if (codigo_tecla_presionada == 13 && $("#cantidad").val() == "") {
      $("#cantidad").select();
      return false;
    }

    if (validar_input_numerico($(this))) {
      precio_unitario = parseFloat($(this).val());

      calcular_valor_descuento();

      calcular_impuestos();

      calcular_precio_total();

      if (codigo_tecla_presionada == 13) {
        $("#tasa_descuento").focus();
      }
    } else {
      $(this).focus();
      return false;
    }
  });

  /**
   * 
   */
  $("#tasa_descuento").keyup(function () {
    if (validar_input_numerico($(this))) {
      tasa_descuento = parseFloat($(this).val());

      var codigo_tecla_presionada = event.which || event.keyCode;
      if (codigo_tecla_presionada == 13) {
        agregar_nueva_linea();
        $("#inv_producto_id").focus();
        return true;
      }

      // máximo valor permitido = 100
      if ($(this).val() > 100) {
        $(this).val(100);
      }

      calcular_valor_descuento();
      calcular_impuestos();
      calcular_precio_total();
    } else {
      $(this).focus();
      return false;
    }
  });

  function reset_descuento() {
    $("#tasa_descuento").val(0);
    calcular_valor_descuento();
  }

  /**
   * SET CATALOGOS
   */
  set_catalogos( $('#pdv_id').val() );
  
  // Nuevo
  if ( $('#msj_resolucion_facturacion').val() != '') {
      Swal.fire({
        icon: 'error',
        title: 'Alerta!',
        text: $('#msj_resolucion_facturacion').val()
      });
  }

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

  /*
   ** Al eliminar una fila
   */
  $(document).on("click", ".btn_eliminar", function (event) {
    event.preventDefault();
    var fila = $(this).closest("tr");

    fila.remove();

    calcular_totales();

    hay_productos--;
    numero_linea--;
    $("#numero_lineas").text(hay_productos);
    set_cantidades_ingresadas();

    $("#total_valor_total").actualizar_medio_recaudo();
    reset_linea_ingreso_default();

    if (hay_productos == 0) {
      reset_efectivo_recibido();

      if ($("#pedido_id").val() != 0) {
        $("#pedido_id").val(0);
        $("#btn_cancelar").show();
        $("#btn_cancelar_pedido").hide();
        $("#object_anticipos").val("null");
        reset_campos_formulario();
      }
    }

    $('#inv_producto_id').focus();

  });

  /**
   * 
   */
  $("#btn_probar").click(function (event) {
    event.preventDefault();
    llenar_tabla_productos_facturados();
  });

  // BOTON GUARDAR EL FORMULARIO
  $("#btn_guardar_factura").click(function (event) {

    event.preventDefault();
    if (locked) {
      console.log('Bloqueado!!!');
      return false;
    }

        // Desactivar el click del bot�n
    activar_estado_boton_guardando_factura();

    // Cede un ciclo al navegador para que pinte el spinner antes del trabajo pesado.
    setTimeout(function () {
      try {
      if (hay_productos == 0) {
        Swal.fire({
          icon: "error",
          title: "Alerta!",
          text: "No ha ingresado productos.",
        });
        reset_linea_ingreso_default();
        reset_efectivo_recibido();
        $("#btn_nuevo").hide();
        reset_estado_boton_guardar_factura();
        return false;
      }

      if (!validar_producto_con_contorno()) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia!',
            text: 'Has ingresado productos que necesitan Contorno, pero NO está agregado el Contorno.'
        });
        reset_estado_boton_guardar_factura();
        return false;
      }

      var manejar_propinas = ($("#manejar_propinas").val() == 1);
      var manejar_datafono = ($("#manejar_datafono").val() == 1);

      if (manejar_propinas) {
        if ($("#valor_propina").val() != 0) {
          if (!permitir_guardar_factura_con_propina()) {
            reset_estado_boton_guardar_factura();
            return false;
          }
        }
      }

      if (manejar_datafono) {
        if ($("#valor_datafono").val() != 0) {
          if (!permitir_guardar_factura_con_datafono()) {
            reset_estado_boton_guardar_factura();
            return false;
          }
        }
      }

      $("#linea_ingreso_default").remove();

      var table = $("#ingreso_registros").tableToJSON();
      table = add_impuesto_id_to_table_lines(table);
      
      json_table2 = get_json_registros_medios_recaudo();

      if (manejar_propinas) {
        // Si hay propina, siempre va a venir una sola linea de medio de pago
        json_table2 = separar_json_linea_medios_recaudo(json_table2);
      }

      if (manejar_datafono) {
        // Si hay Comision por datafono, siempre va a venir una sola linea de medio de pago
        json_table2 = separar_json_linea_medios_recaudo(json_table2);
      }

      // Se asigna el objeto JSON a un campo oculto del formulario
      $("#lineas_registros").val(JSON.stringify(table));
      $("#lineas_registros_medios_recaudos").val(json_table2);
      update_request_id();

      // Nota: No se puede enviar controles disabled
      var data = $("#form_create").serialize();

      if (manejar_propinas) {
        data += "&valor_propina=" + $("#valor_propina").val();
      }

      if (manejar_datafono) {
        data += "&valor_datafono=" + $("#valor_datafono").val();
      }

      var tiempo_espera_guardar_factura = 7000; // 7 segundos
      var tiempo_espera_config = parseInt($('#tiempo_espera_guardar_factura').val(), 10);
      if (!isNaN(tiempo_espera_config) && tiempo_espera_config > 0) {
        tiempo_espera_guardar_factura = tiempo_espera_config * 1000;
      }

      var url = $("#form_create").attr("action");
      var watchdog_guardado_ms = tiempo_espera_guardar_factura + 5000;
      // Mitiga colisiones al duplicar pestanas: el primer guardado de la pestana
      // fuerza un nuevo contexto de intento antes de enviar.
      if (!pos_save_attempted) {
        update_uniqid();
        update_request_id();
      }
    
      // Comprobamos si el semaforo esta en verde (1)
      if (!locked) {
        // No esta bloqueado aun, bloqueamos, preparamos y enviamos la peticion
        iniciar_watchdog_guardado(watchdog_guardado_ms);
        $.ajax({
          url: url,
          data: data,
          type: 'POST',
          async: true,
          cache: false,
          timeout: tiempo_espera_guardar_factura,
          beforeSend: function(){ 
            locked = true;
            pos_save_attempted = true;
          },
          success: function(doc_encabezado){
            try {
              finalizar_almacenamiento_factura(doc_encabezado);
            } catch (e) {
              reset_estado_boton_guardar_factura();
              Swal.fire({
                icon: "error",
                title: "Factura guardada con error en pantalla",
                text: "Se presento un error al finalizar el proceso en navegador. Verifica en el historial si la factura quedo creada."
              });
              console.error(e);
            }
          },
          error: function(xhr){
            reset_estado_boton_guardar_factura();

            var status_text = (xhr && typeof xhr.statusText === 'string') ? xhr.statusText : ''; // Respuesta siempre
            var response_text = (xhr && typeof xhr.responseText === 'string') ? xhr.responseText : ''; // Solo cuando hay respuesta del servidor
            var server_error_code = (xhr && typeof xhr.status !== 'undefined') ? xhr.status : 0; // Código de error HTTP. 0 cuando no hay respuesta del servidor
            var response_json = (xhr && typeof xhr.responseJSON === "object") ? xhr.responseJSON : null;
            var request_id_error = $("#request_id").val() || "";
            if (response_json && typeof response_json.request_id === "string" && response_json.request_id !== "") {
              request_id_error = response_json.request_id;
            }
            var request_id_sufijo = request_id_error !== "" ? (" Ref: " + request_id_error) : "";

            let position = status_text.search("NetworkError");
            if (position != -1) {
              var error_label = 'Error ' + server_error_code + '. Pérdida de conexión de INTERNET.';
              Swal.fire({
                icon: 'error',
                title: '1. FACTURA NO GUARDADA. INTENTA OTRA VEZ!',
                text: error_label + request_id_sufijo
              }); 
              
              puede_continuar = false;

              return false;
            }

            // jQuery devuelve código 0 en timeout, abort o cuando no hay conexión.
            if (server_error_code === 0) {
              var error_label = 'Error 0. No hubo respuesta del servidor (timeout o conexión interrumpida).';
              Swal.fire({
                icon: 'error',
                title: '1. FACTURA NO GUARDADA. INTENTA OTRA VEZ!',
                text: error_label + request_id_sufijo
              });
               
              puede_continuar = false;

              return false;
            }

            if (server_error_code === 409) {
              var response_json = (xhr && typeof xhr.responseJSON === "object") ? xhr.responseJSON : null;
              var warning_message = "Los pedidos seleccionados ya no están disponibles para facturar. Actualiza la lista de pendientes.";
              if (response_json && typeof response_json.message === "string" && response_json.message !== "") {
                warning_message = response_json.message;
              }

              Swal.fire({
                icon: "warning",
                title: "Advertencia",
                text: warning_message + request_id_sufijo
              });

              puede_continuar = false;
              return false;
            }

            // Si hay respuesta del servidor, pero hay un error de Laravel
            position = response_text.search("Duplicate entry");
            
            if (position != -1) { // -1 la Cadena no existe
              var error_label = 'Error ' + server_error_code + '. Validación del servidor. Duplicate entry.';
              Swal.fire({
                icon: 'warning',
                title: 'Validación ¡NO CIERRES! Solo haz clic en el BOTÓN NEGRO REFRESH y continúa con la facturación.',
                text: error_label + request_id_sufijo 
              });
              
              puede_continuar = false;

              return false;
            }

            // Cuando se cerró la sesión del usuario
            position = response_text.search("Trying to get property &#039;email&#039; of non-object");
            
            if (position != -1) { // -1 la Cadena no existe
              var error_label = 'Error ' + server_error_code + '. Validación del servidor. Se cerró la sesión inesperadamente.';
              Swal.fire({
                icon: 'warning',
                title: '4. FACTURA NO GUARDADA. ¡NO CIERRES! Inicia sesión en otra pestaña y continúa con la facturación.',
                text: error_label + request_id_sufijo 
              });
              
              puede_continuar = false;

              return false;
            }
            
            if (server_error_code == 500) { // Error Interno del Servidor
              var error_label = 'Error ' + server_error_code + '. Validación del servidor. Por favor, comuníquese con soporte.';
              Swal.fire({
                icon: 'error',
                title: '2. FACTURA NO GUARDADA. ERROR EN EL SERVIDOR.',
                text: error_label + request_id_sufijo 
              });
              
              puede_continuar = false;

              return false;
            }
            
            if (server_error_code != 500) { // Error Interno del Servidor
              var error_label = 'Cód. de respuesta ' + server_error_code + '. La solicitud no pudo ser procesada.';
              Swal.fire({
                icon: 'error',
                title: '3. FACTURA NO GUARDADA. POR FAVOR, ACTUALIZAR LA PÁGINA E INTENTAR NUEVAMENTE.',
                text: error_label + request_id_sufijo 
              });
              
              puede_continuar = false;

              return false;
            }
          },
          complete: function(){ 
            limpiar_watchdog_guardado();
            locked = false;  
          }
        });
      } else {
        // Bloqueado!!!
        console.log('Bloqueado!!!');
        reset_estado_boton_guardar_factura();
      }
      } catch (e) {
        reset_estado_boton_guardar_factura();
        Swal.fire({
          icon: "error",
          title: "Error al preparar el guardado",
          text: "Ocurrio un error inesperado en navegador. Intenta guardar nuevamente."
        });
        console.error(e);
      }
    }, 0);
  });

  function finalizar_almacenamiento_factura( doc_encabezado )
  {
    if (parseInt(doc_encabezado.reused_uniqid || 0, 10) === 1) {
      reset_estado_boton_guardar_factura();
      var request_id_reused = (doc_encabezado.request_id || "").toString();
      var texto_request_id = request_id_reused !== "" ? (" Ref: " + request_id_reused) : "";
      Swal.fire({
        icon: "warning",
        title: "Factura ya guardada",
        text:
          "Se recuperó una factura ya creada con este intento de guardado (" +
          doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo +
          "). Para evitar inconsistencias, consulta e imprime el documento desde el historial." + texto_request_id
      });

      $(".lbl_consecutivo_doc_encabezado").text(doc_encabezado.consecutivo);
      $("#pedido_id").val(0);
      $("#object_anticipos").val("null");
      pos_save_attempted = false;
      update_uniqid();
      resetear_ventana();

      return false;
    }

    $('#cliente_input').css('background-color', '#eee');

    $(".lbl_consecutivo_doc_encabezado").text(doc_encabezado.consecutivo);

    if( $('#mostrar_saldo_pendiente_cxc_al_imprimir').val() == 1 )
    {
      $('#lbl_saldo_pendiente_cxc').text( new Intl.NumberFormat("de-DE").format(  doc_encabezado.saldo_pendiente_cxc.toFixed(0) ) );
    }
    
    llenar_tabla_productos_facturados();

    $('#lbl_creado_por_fecha_y_hora').text('Elaboró: ' + doc_encabezado.lbl_creado_por_fecha_y_hora);

    reset_estado_boton_guardar_factura();
    enviar_impresion( doc_encabezado );
    
    $("#pedido_id").val(0);
    $("#object_anticipos").val('null');
    pos_save_attempted = false;
    update_uniqid();

    return false;
  }

  $("#btn_update_uniqid").click(function (event) {
    event.preventDefault();
    update_uniqid();
  });  

  function update_uniqid()
  {
    $("#uniqid").val( uniqid() );
    update_request_id();

    if ($("#draft_id").length > 0) {
      $("#draft_id").val( crear_identificador_unico_pos() );

      var draft_key = get_pos_tab_storage_prefix() + "draft_id";
      sessionStorage.setItem(draft_key, $("#draft_id").val());
    }
  }

  // Lupa
  $("#btn_listar_items").click(function (event) {

    $("#popup_alerta").hide();

    $("#myModal").modal({ keyboard: true });
    $(".btn_edit_modal").hide();
    $(".btn_edit_modal").hide();
    $("#myTable_filter").find("input").css("border", "3px double red");
    $("#myTable_filter").find("input").focus();
    set_precios_lbl_items();
  });

  /**
   * Al hacer click en el botón de registrar ingresos o gastos
   */
  $(document).on("click", ".btn_registrar_ingresos_gastos", function (event) {
    event.preventDefault();

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ backdrop: "static" });

    $("#myModal2 .modal-title").text(
      "Nuevo registro de " + $(this).attr("data-lbl_ventana")
    );

    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn-danger").hide();
    $("#myModal2 .btn_save_modal").show();

    $("#myModal2 .btn_save_modal").removeAttr("disabled");

    var url =
      url_raiz +
      "/" +
      "ventas_pos_form_registro_ingresos_gastos" +
      "/" +
      $("#pdv_id").val() +
      "/" +
      $(this).attr("data-id_modelo") +
      "/" +
      $(this).attr("data-id_transaccion");

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
    }); /**/
  });

  /**
   * Al hacer click en el botón de consultar estado del PDV
   */
  $(document).on("click", ".btn_consultar_estado_pdv", function (event) {
    event.preventDefault();

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ backdrop: "static", keyboard: false });

    $("#myModal2 .modal-title").text(
      "Consulta de " + $(this).attr("data-lbl_ventana")
    );

    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn_save_modal").hide();

    var url =
      url_raiz +
      "/" +
      "pos_get_saldos_caja_pdv" +
      "/" +
      $("#pdv_id").val() +
      "/" +
      $("#fecha").val() +
      "/" +
      $("#fecha").val();

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
    }); /**/
  });

  /**
   * Al hacer click en el botón de revisar pedidos de ventas
   */
  $(document).on("click", ".btn_revisar_pedidos_ventas", function (event) {
    event.preventDefault();

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ keyboard: true });

    $("#myModal2 .modal-title").text(
      "Consulta de " + $(this).attr("data-lbl_ventana")
    );

    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn_save_modal").hide();

    var url =
      url_raiz + "/" + "pos_revisar_pedidos_ventas" + "/" + $("#pdv_id").val();

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
      document.getElementById("mySearchInput").focus();
      
    }); /**/
  });

  /**
   * Al hacer click en el botón de guardar el registro de ingresos o gastos
   */
  $(document).on("click", "#myModal2 .btn_save_modal", function (event) {
    event.preventDefault();

    if ($("#combobox_motivos").val() == "") {
      $("#combobox_motivos").focus();
      alert("Debe ingresar un Motivo");
      return false;
    }

    if ($("#cliente_proveedor_id").val() == "") {
      $("#cliente_proveedor_id").focus();
      alert("Debe ingresar un Cliente/Proveedor.");
      return false;
    }
    if (
      !validar_input_numerico($("#col_valor")) ||
      $("#col_valor").val() == ""
    ) {

      alert("No ha ingresado una valor para la transacción.");
      return false;
    }

    // Desactivar el click del botón
    $(this).hide();
    $(this).attr("disabled", "disabled");

    var url = $("#form_registrar_ingresos_gastos").attr("action");
    var data = $("#form_registrar_ingresos_gastos").serialize();

    $.post(url, data, function (respuesta) {
      $("#contenido_modal2").html(respuesta);
      $("#myModal2 .btn-danger").show();
      $("#myModal2 .btn_save_modal").hide();
    });
  });

  /**
   * Al hacer click en el botón de consultar documentos pendientes
   */
  $(document).on("click", ".btn_consultar_documentos", function (event) {
    event.preventDefault();

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ keyboard: true });

    $("#myModal2 .modal-title").text(
      "Consulta de " + $(this).attr("data-lbl_ventana")
    );

    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn_save_modal").hide();

    var url =
      url_raiz +
      "/" +
      "pos_consultar_documentos_pendientes" +
      "/" +
      $("#pdv_id").val() +
      "/" +
      $("#fecha").val() +
      "/" +
      $("#fecha").val();

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
    }); /**/
  });

  var fila;
  $(document).on("click", ".btn_anular_factura", function (event) {
    event.preventDefault();

    var opcion = confirm(
      "¿Seguro desea anular la factura " +
        $(this).attr("data-lbl_factura") +
        " ?"
    );

    if (opcion) {
      fila = $(this).closest("tr");

      $("#div_spin2").fadeIn();
      var url =
        url_raiz +
        "/" +
        "pos_factura_anular" +
        "/" +
        $(this).attr("data-doc_encabezado_id");

      $.get(url, function (respuesta) {
        $("#div_spin2").hide();

        fila.find("td").eq(8).text("Anulado");
        fila.find(".btn_modificar_factura").hide();
        fila.find(".btn_anular_factura").hide();
        alert("Documento anulado correctamente.");
      });
    } else {
      return false;
    }
  });
  
  /**
   * Al hacer click en el botón de anular factura contabilizada
   */
  $(document).on("click", ".btn_anular_factura_contabilizada", function (event) {
    event.preventDefault();

    var opcion = confirm(
      "¿Seguro desea anular la factura " +
        $(this).attr("data-lbl_factura") +
        "?"
    );

    if (opcion) {
      fila = $(this).closest("tr");

      $("#div_spin2").fadeIn();
      var url =
        url_raiz +
        "/" +
        "pos_anular_factura_contabilizada" +
        "/" +
        $(this).attr("data-doc_encabezado_id");

      $.get(url, function (respuesta) {
        $("#div_spin2").hide();

        if ( respuesta.status == "error" ) {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: respuesta.message
          });
          return false;          
        }

        fila.find("td").eq(8).text("Anulado");
        fila.find(".btn_modificar_factura").hide();
        fila.find(".btn_anular_factura_contabilizada").hide();
        alert("Documento anulado correctamente.");
      });
    } else {
      return false;
    }
  });
  
  // Al mostrar la ventana modal
  $("#recaudoModal").on("shown.bs.modal", function () {
    $("#form_registro").before(
        '<div id="div_pendiente_ingresar_medio_recaudo" style="color: red;">Pendiente por registrar: <span id="lbl_vlr_pendiente_ingresar">$ 0</span><div>'
    );
    set_valor_pendiente_ingresar_medios_recaudos();
  });
  
  // Al OCULTAR la ventana modal
  $("#recaudoModal").on("hidden.bs.modal", function () {
    $("#div_pendiente_ingresar_medio_recaudo").remove();
  });

  /**
   * Al hacer click en el botón de borrar propina
   */
  $(document).on("click", ".btn_borrar_propina", function (event) {
    event.preventDefault();

    var opcion = confirm(
      "¿Seguro desea borrar la propina de la factura " +
        $(this).attr("data-lbl_factura") +
        " ?"
    );

    if (opcion) {
      fila = $(this).closest("tr");

      $("#div_spin2").fadeIn();
      var url =
        url_raiz +
        "/" +
        "pos_factura_borrar_propina" +
        "/" +
        $(this).attr("data-doc_encabezado_id");

      $.get(url, function (respuesta) {
        $("#div_spin2").hide();

        alert("Propina borrada correctamente de la factura.");
        $("#contenido_modal2").html("");
        $("#myModal2").modal("hide");
      });
    } else {
      return false;
    }
  });
  
});


