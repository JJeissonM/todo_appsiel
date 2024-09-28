var hay_productos = 0;
var url_raiz, redondear_centena, numero_linea;
var productos,
  precios,
  descuentos,
  clientes,
  cliente_default,
  forma_pago_default,
  fecha_vencimiento_default,
  inv_producto_id;

$("#btn_nuevo").hide();
$("#btnPaula").hide();

function ejecutar_acciones_con_item_sugerencia(
  item_sugerencia,
  obj_text_input
) {
  $(".text_input_sugerencias").select();
}

$.fn.validar_fecha_diferente = function () {
  if ($("#fecha").val() != get_fecha_hoy()) {
    $("#msj_fecha_diferente").show();
  } else {
    $("#msj_fecha_diferente").hide();
  }
};

function get_json_registros_medios_recaudo() {
  var table2 = $("#ingreso_registros_medios_recaudo").tableToJSON();
  var json_table2 = "";
  if (table2.length == 1) {
    // Solo tiene la linea de totales
    var json_table2 =
      '[{"teso_medio_recaudo_id":"1-Efectivo","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"' +
      $("#caja_pdv_default_id").val() +
      "-" +
      $("#caja_pdv_default_label").val() +
      '","teso_cuenta_bancaria_id":"0-","valor":"$' +
      $("#valor_total_factura").val() +
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

function llenar_tabla_productos_facturados() {
  var linea_factura, linea_factura2;
  var lbl_total_factura = 0;
  var lbl_base_impuesto_total = 0;
  var lbl_valor_impuesto = 0;

  var cantidad_total_productos = 0;

  $(".linea_registro").each(function () {
    linea_factura =
      "<tr> <td> " +
      $(this).find(".lbl_producto_descripcion").text() +
      " </td> <td> " +
      $(this).find(".cantidad").text() +
      " " +
      $(this).find(".lbl_producto_unidad_medida").text() +
      " ($" +
      $(this).find(".elemento_modificar").eq(1).text() +
      ") </td> <td> " +
      $(this).find(".lbl_tasa_impuesto").text() +
      "</td> <td> " +
      $(this).find(".lbl_precio_total").text() +
      "  </td></tr>";

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
        $(this).find(".elemento_modificar").eq(1).text() +
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

    cantidad_total_productos++;
  });

  var total_factura_redondeado = $.fn.redondear_a_centena(lbl_total_factura);

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
        $.fn.redondear_a_centena(lbl_base_impuesto_total)
      )
  );

  $(".lbl_valor_impuesto").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        $.fn.redondear_a_centena(lbl_valor_impuesto)
      )
  );

  $(".lbl_ajuste_al_peso").text(
    "$ " + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso)
  );
  $(".lbl_total_recibido").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        parseFloat($("#efectivo_recibido").val())
      )
  );
  $(".lbl_total_cambio").text(
    "$ " +
      new Intl.NumberFormat("de-DE").format(
        $.fn.redondear_a_centena(total_cambio)
      )
  );

  $(".lbl_condicion_pago").text($("#forma_pago").val());
  $(".lbl_fecha_vencimiento").text($("#fecha_vencimiento").val());

  $("#cantidad_total_productos").text(cantidad_total_productos);

  $("#tr_fecha_vencimiento").hide();
  if ($("#forma_pago").val() == "credito") {
    $("#tr_fecha_vencimiento").show();
    $("#tr_total_recibido").hide();
    $("#tr_total_cambio").hide();
  }

  $("#lbl_fecha").text($("#fecha").val());
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

  llenar_resumen_medios_recaudo();
}

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
          "</td><td>" +
          lbl_valor_medio_pago +
          "</td></tr>"
      );
  } else {
    $("#ingreso_registros_medios_recaudo > tbody > tr").each(function () {
      var array_celdas = $(this).find("td");
      var lbl_medio_pago = array_celdas.eq(0).find("span").eq(1).text();
      var lbl_caja_banco =
        array_celdas.eq(2).find("span").eq(1).text() +
        "" +
        array_celdas.eq(3).find("span").eq(1).text();
      var lbl_valor_medio_pago = array_celdas.eq(4).text();

      $("#tabla_resumen_medios_pago")
        .find("tbody:last")
        .append(
          "<tr><td>" +
            lbl_medio_pago +
            "</td><td>" +
            lbl_caja_banco +
            "</td><td>" +
            lbl_valor_medio_pago +
            "</td></tr>"
        );
    });
  }
}

function resetear_ventana() {
  $("#tabla_productos_facturados").find("tbody").html("");
  $("#tabla_productos_facturados2").find("tbody").html("");

  reset_campos_formulario();
  reset_tabla_ingreso_items();
  reset_resumen_de_totales();
  reset_linea_ingreso_default();
  reset_tabla_ingreso_medios_pago();
  reset_efectivo_recibido();

  $("#btn_cancelar").show();
  $("#btn_cancelar_pedido").hide();

  $("#lbl_ajuste_al_peso").text("$ ");

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

    $.fn.reset_propina();
  }

  if ($("#manejar_datafono").val() == 1) {
    $("#valor_sub_total_factura").val(0);
    $("#lbl_sub_total_factura").text("$ 0");

    $.fn.reset_datafono();
  }
}

function reset_tabla_ingreso_items() {
  $(".linea_registro").each(function () {
    $(this).remove();
  });
  hay_productos = 0;
  numero_lineas = 0;
  $("#numero_lineas").text("0");
}

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

function reset_tabla_ingreso_medios_pago() {
  $("#ingreso_registros_medios_recaudo").find("tbody").html("");

  $("#tabla_resumen_medios_pago").find("tbody").html("");

  // reset totales
  $("#total_valor_total").text("$0.00");
}

function reset_linea_ingreso_default() {
  $("#efectivo_recibido").val("");

  $("#inv_producto_id").val("");
  $("#cantidad").val("");
  $("#precio_unitario").val("");
  $("#tasa_descuento").val("");
  $("#tasa_impuesto").val("");
  $("#precio_total").val("");

  $("#inv_producto_id").focus();

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

function reset_efectivo_recibido() {
  $("#efectivo_recibido").val("");
  $("#total_efectivo_recibido").val(0);
  $("#lbl_efectivo_recibido").text("$ 0");
  $("#total_cambio").text("$ 0");
  //$('#lbl_ajuste_al_peso').text('$ ');
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

$(document).ready(function () {
  if ($("#action").val() != "create") {
    reset_efectivo_recibido();
    $("#btn_nuevo").show();
  }

  if ($("#action").val() != "edit") {
    $("#fecha").val(get_fecha_hoy());
  }

  $.fn.validar_fecha_diferente();

  $("#fecha").on("change", function () {
    $.fn.validar_fecha_diferente();
  });

  //Al hacer click en alguna de las sugerencias (escoger un cliente)
  $(document).on("click", ".list-group-item-cliente", function () {
    seleccionar_cliente($(this));

    return false;
  });

  //Al hacer click en la sugerencia Crear nuevo
  $(document).on("click", ".list-group-item-sugerencia", function () {
    $("#cliente_input").val(cliente_default.descripcion);
    $("#cliente_input").css("background-color", "transparent");

    $("#clientes_suggestions").html("");
    $("#clientes_suggestions").hide();

    if ($(this).attr("data-accion") == "crear_nuevo_registro") {
      window.open($(this).attr("href"), "_blank");
    }

    return false;
  });

  $('[data-toggle="tooltip"]').tooltip();
  var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
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

    if (campo_busqueda == "id" || campo_busqueda == "referencia") {
      $("#cantidad").select();
    } else {
      // Por código de barras, se agrega la línea con un unidad de producto
      $("#cantidad").val(1);
      cantidad = 1;

      // Para balazas Dibal, obtener la cantidad del mismo codigo de barras
      if (
        $("#forma_lectura_codigo_barras").val() == "codigo_cantidad" &&
        barcode.substr(0, 1) == 0
      ) {
        $("#cantidad").val(get_quantity_from_barcode(barcode));
        cantidad = parseFloat(get_quantity_from_barcode(barcode));
        if (barcode_precio_unitario != "") {
          $("#precio_unitario").val(barcode_precio_unitario);
        }
      }

      calcular_valor_descuento();
      calcular_impuestos();
      calcular_precio_total();
      agregar_nueva_linea(); // Solo cuando es por Codigo de barras
    }
  }

  function get_item_id_from_barcode(barcode) {
    return parseInt(barcode.substr(0, 6));
  }

  function get_quantity_from_barcode(barcode) {
    return barcode.substr(6, 2) + "." + barcode.substr(8, 3);
  }

  $("#efectivo_recibido").on("keyup", function (event) {
    $("#popup_alerta").hide();
    var codigo_tecla_presionada = event.which || event.keyCode;

    if (codigo_tecla_presionada == 27) {
      $("#inv_producto_id").focus();
      return false;
    }

    if (codigo_tecla_presionada == 113) {
      // 113: F2
      $("#textinput_filter_item").select();
      return false;
    }

    if ($("#valor_total_factura").val() <= 0) {
      return false;
    }

    if (validar_input_numerico($(this)) && $(this).val() > 0) {
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
          $("#total_efectivo_recibido").val($(this).val());
          $.fn.set_label_efectivo_recibido($(this).val());

          $.fn.calcular_total_cambio($(this).val());

          $.fn.activar_boton_guardar_factura();

          $.fn.cambiar_estilo_div_total_cambio();

          break;
      }
    } else {
      return false;
    }
  });

  /*
   ** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
   */
  var ir_al_precio_total = 0;
  $("#cantidad").keyup(function (event) {
    var codigo_tecla_presionada = event.which || event.keyCode;

    if (codigo_tecla_presionada == 13 && $(this).val() == "") {
      $("#precio_unitario").select();
      return false;
    }

    if (validar_input_numerico($(this)) && $(this).val() > 0) {
      cantidad = parseFloat($(this).val());

      if (codigo_tecla_presionada == 13) {
        // ENTER
        agregar_nueva_linea();
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

  function validar_venta_menor_costo() {
    // Descuento del 100%
    if (precio_unitario == valor_unitario_descuento) {
      $("#popup_alerta").hide();
      return true;
    }

    if ($("#permitir_venta_menor_costo").val() == 0) {
      var ok = true;

      if (base_impuesto_unitario < costo_unitario) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text(
          "El precio está por debajo del costo de venta del producto." +
            " $" +
            new Intl.NumberFormat("de-DE").format(costo_unitario.toFixed(2)) +
            " + IVA"
        );
        ok = false;
      } else {
        $("#popup_alerta").hide();
        ok = true;
      }
    } else {
      $("#popup_alerta").hide();
      ok = true;
    }

    return ok;
  }

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

  // Valores unitarios
  function calcular_impuestos() {
    var precio_venta = precio_unitario - valor_unitario_descuento;

    base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

    valor_impuesto_unitario = precio_venta - base_impuesto_unitario;
  }

  $("#tasa_descuento").keyup(function () {
    if (validar_input_numerico($(this))) {
      tasa_descuento = parseFloat($(this).val());

      var codigo_tecla_presionada = event.which || event.keyCode;
      if (codigo_tecla_presionada == 13) {
        agregar_nueva_linea();
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

  function calcular_valor_descuento() {
    // El descuento se calcula cuando el precio tiene el IVA incluido
    valor_unitario_descuento = (precio_unitario * tasa_descuento) / 100;
    valor_total_descuento = valor_unitario_descuento * cantidad;
  }

  function reset_descuento() {
    $("#tasa_descuento").val(0);
    calcular_valor_descuento();
  }

  function seleccionar_cliente(item_sugerencia) {
    // Asignar descripción al TextInput
    $("#cliente_input").val(item_sugerencia.html());
    $("#cliente_input").css("background-color", "transparent");

    // Asignar Campos ocultos
    $("#cliente_id").val(item_sugerencia.attr("data-cliente_id"));
    $("#zona_id").val(item_sugerencia.attr("data-zona_id"));
    $("#clase_cliente_id").val(item_sugerencia.attr("data-clase_cliente_id"));
    $("#liquida_impuestos").val(item_sugerencia.attr("data-liquida_impuestos"));
    $("#core_tercero_id").val(item_sugerencia.attr("data-core_tercero_id"));
    $("#lista_precios_id").val(item_sugerencia.attr("data-lista_precios_id"));
    $("#lista_descuentos_id").val(
      item_sugerencia.attr("data-lista_descuentos_id")
    );

    // Asignar resto de campos
    $("#vendedor_id").val(item_sugerencia.attr("data-vendedor_id"));
    $("#vendedor_id").attr(
      "data-vendedor_descripcion",
      item_sugerencia.attr("data-vendedor_descripcion")
    );
    $(".vendedor_activo").attr("class", "btn btn-default btn_vendedor");
    $(
      "button[data-vendedor_id='" +
        item_sugerencia.attr("data-vendedor_id") +
        "']"
    ).attr("class", "btn btn-default btn_vendedor vendedor_activo");
    $(document).prop(
      "title",
      item_sugerencia.attr("data-vendedor_descripcion")
    );

    $("#inv_bodega_id").val(item_sugerencia.attr("data-inv_bodega_id"));

    $("#cliente_descripcion").val(item_sugerencia.attr("data-nombre_cliente"));
    $("#cliente_descripcion_aux").val(
      item_sugerencia.attr("data-nombre_cliente")
    );
    $("#numero_identificacion").val(
      item_sugerencia.attr("data-numero_identificacion")
    );
    $("#email").val(item_sugerencia.attr("data-email"));
    $("#direccion1").val(item_sugerencia.attr("data-direccion1"));
    $("#telefono1").val(item_sugerencia.attr("data-telefono1"));

    var forma_pago = "contado";
    var dias_plazo = parseInt(item_sugerencia.attr("data-dias_plazo"));
    if (dias_plazo > 0) {
      forma_pago = "credito";
    }
    $("#forma_pago").val(forma_pago);

    // Para llenar la fecha de vencimiento
    var fecha = new Date($("#fecha").val());
    fecha.setDate(fecha.getDate() + (dias_plazo + 1));

    var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
    var dia = fecha.getDate(); // + 1; // Se le suma 1,

    if (mes < 10) {
      mes = "0" + mes;
    }

    if (dia < 10) {
      dia = "0" + dia;
    }
    $("#fecha_vencimiento").val(fecha.getFullYear() + "-" + mes + "-" + dia);

    //Hacemos desaparecer el resto de sugerencias
    $("#clientes_suggestions").html("");
    $("#clientes_suggestions").hide();

    //reset_tabla_ingreso_items();
    //reset_resumen_de_totales();
    reset_linea_ingreso_default();

    $.get(
      url_raiz +
        "/vtas_get_lista_precios_cliente" +
        "/" +
        $("#cliente_id").val()
    ).done(function (data) {
      precios = data[0];
      descuentos = data[1];
    });

    if (!$.isNumeric(parseInt($("#core_tercero_id").val()))) {
      Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "Error al seleccionar el cliente. Ingrese un cliente correcto.",
      });
    }

    $.fn.activar_boton_guardar_factura();

    // Bajar el Scroll hasta el final de la página
    //$("html, body").animate({scrollTop: $(document).height() + "px"});
  }

  function agregar_nueva_linea() {
    if (!calcular_precio_total()) {
      $("#popup_alerta").show();
      $("#popup_alerta").css("background-color", "red");
      $("#popup_alerta").text("Error en precio total. Por favor verifique");
      return false;
    }

    agregar_la_linea();
  }

  function agregar_la_linea() {
    if (!validar_venta_menor_costo()) {
      return false;
    }

    $("#popup_alerta").hide();
    $("#existencia_actual").html("");
    $("#existencia_actual").hide();

    if (!$.isNumeric(parseInt($("#core_tercero_id").val()))) {
      Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "Error al seleccionar el cliente. Ingrese un cliente correcto.",
      });

      return false;
    }

    var string_fila = $.fn.generar_string_celdas();

    if (string_fila == false) {
      $("#popup_alerta").show();
      $("#popup_alerta").css("background-color", "red");
      $("#popup_alerta").text("Producto no encontrado.");
      return false;
    }

    // agregar nueva fila a la tabla
    $("#ingreso_registros")
      .find("tbody:last")
      .append(
        '<tr class="linea_registro" data-numero_linea="' +
          numero_linea +
          '">' +
          string_fila +
          "</tr>"
      );

    // Se calculan los totales
    calcular_totales();

    hay_productos++;
    $("#btn_nuevo").show();
    $("#numero_lineas").text(hay_productos);
    //deshabilitar_campos_encabezado();

    reset_linea_ingreso_default();
    reset_efectivo_recibido();

    $("#total_valor_total").actualizar_medio_recaudo();

    numero_linea++;
    $("#efectivo_recibido").removeAttr("readonly");

    if ($("#manejar_platillos_con_contorno").val() == 1) {
      reset_component_items_contorno();
    }
  }

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

    $("#total_valor_total").actualizar_medio_recaudo();
    reset_linea_ingreso_default();

    if (hay_productos == 0) {
      reset_efectivo_recibido();
    }
  });

  $("#btn_probar").click(function (event) {
    event.preventDefault();
    llenar_tabla_productos_facturados();
  });

  // BOTON GUARDAR EL FORMULARIO
  $("#btn_guardar_factura").click(function (event) {
    event.preventDefault();

    if (hay_productos == 0) {
      Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "No ha ingresado productos.",
      });
      reset_linea_ingreso_default();
      reset_efectivo_recibido();
      $("#btn_nuevo").hide();
      return false;
    }

    if ($("#manejar_propinas").val() == 1) {
      if ($("#valor_propina").val() != 0) {
        if (!$.fn.permitir_guardar_factura_con_propina()) {
          return false;
        }
      }
    }

    if ($("#manejar_datafono").val() == 1) {
      if ($("#valor_datafono").val() != 0) {
        if (!$.fn.permitir_guardar_factura_con_datafono()) {
          return false;
        }
      }
    }

    // Desactivar el click del botón
    $(this).html('<i class="fa fa-spinner fa-spin"></i> Guardando');
    $(this).attr("disabled", "disabled");
    $(this).attr("id", "btn_guardando");

    $("#linea_ingreso_default").remove();

    var table = $("#ingreso_registros").tableToJSON();

    json_table2 = get_json_registros_medios_recaudo();

    if ($("#manejar_propinas").val() == 1) {
      // Si hay propina, siempre va a venir una sola linea de medio de pago
      json_table2 = $.fn.separar_json_linea_medios_recaudo(json_table2);
    }

    if ($("#manejar_datafono").val() == 1) {
      // Si hay Comision por datafono, siempre va a venir una sola linea de medio de pago
      json_table2 = $.fn.separar_json_linea_medios_recaudo(json_table2);
    }

    // Se asigna el objeto JSON a un campo oculto del formulario
    $("#lineas_registros").val(JSON.stringify(table));
    $("#lineas_registros_medios_recaudos").val(json_table2);

    // Nota: No se puede enviar controles disabled

    var url = $("#form_create").attr("action");
    var data = $("#form_create").serialize();

    if ($("#manejar_propinas").val() == 1) {
      data += "&valor_propina=" + $("#valor_propina").val();
    }

    if ($("#manejar_datafono").val() == 1) {
      data += "&valor_datafono=" + $("#valor_datafono").val();
    }

    $.post(url, data, function (doc_encabezado) {
      $("#btn_guardando").html('<i class="fa fa-check"></i> Guardar factura');
      $("#btn_guardando").attr("id", "btn_guardar_factura");
      $("title").append(doc_encabezado.consecutivo);

      $(".lbl_consecutivo_doc_encabezado").text(doc_encabezado.consecutivo);

      llenar_tabla_productos_facturados();

      // Imprimir siempre en cocina
      if ($("#usar_complemento_JSPrintManager").val() == 1) {
        $("#div_formato_impresion_cocina").show();
        print_comanda(doc_encabezado);
        $("#div_formato_impresion_cocina").hide();
      }

      // Preguntar para imprimir en cocina
      if ($("#usar_complemento_JSPrintManager").val() == 2) {
        if (confirm("¿Quiere imprimir una COPIA en la cocina?") == true) {
          $("#div_formato_impresion_cocina").show();
          print_comanda(doc_encabezado);
          $("#div_formato_impresion_cocina").hide();
        }
      }

      $("#msj_ventana_impresion_abierta").show();
      ventana_imprimir();
      resetear_ventana();
      $("#pedido_id").val(0);

      if ($("#action").val() != "create") {
        location.href =
          url_raiz +
          "/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=" +
          $("#pdv_id").val() +
          "&action=create";
      }
    });
  });

  function calcular_precio_total() {
    if (cantidad <= 0 || cantidad == undefined) {
      $("#popup_alerta").show();
      $("#popup_alerta").css("background-color", "red");
      $("#popup_alerta").text("No ha ingresado Cantidad.");
      return false;
    }

    // Descuento del 100%
    if (precio_unitario == valor_unitario_descuento) {
      precio_total = 0;
      $("#precio_total").val(precio_total);
      $("#popup_alerta").hide();
      return true;
    }

    precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

    $("#precio_total").val(0);

    if ($.isNumeric(precio_total) && precio_total >= 0) {
      $("#precio_total").val(precio_total);
      return true;
    } else {
      $("#popup_alerta").hide();
      precio_total = 0;
      return false;
    }
  }

  function calcular_totales() {
    var total_cantidad = 0.0;
    var subtotal = 0.0;
    var valor_total_descuento = 0.0;
    var total_impuestos = 0.0;
    total_factura = 0.0;

    $(".linea_registro").each(function () {
      var cantidad_linea = parseFloat(
        $(this).find(".elemento_modificar").eq(0).text()
      );
      total_cantidad += cantidad_linea;

      subtotal +=
        parseFloat($(this).find(".base_impuesto").text()) * cantidad_linea;
      valor_total_descuento += parseFloat(
        $(this).find(".valor_total_descuento").text()
      );
      total_impuestos +=
        parseFloat($(this).find(".valor_impuesto").text()) * cantidad_linea;
      total_factura += parseFloat($(this).find(".precio_total").text());
    });

    $("#total_cantidad").text(
      new Intl.NumberFormat("de-DE").format(total_cantidad)
    );

    // Subtotal (Sumatoria de base_impuestos por cantidad)
    //var valor = ;
    $("#subtotal").text(
      "$ " +
        new Intl.NumberFormat("de-DE").format(
          (subtotal + valor_total_descuento).toFixed(2)
        )
    );

    $("#descuento").text(
      "$ " +
        new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2))
    );

    // Total impuestos (Sumatoria de valor_impuesto por cantidad)
    $("#total_impuestos").text(
      "$ " + new Intl.NumberFormat("de-DE").format(total_impuestos.toFixed(2))
    );

    // label Total factura  (Sumatoria de precio_total)
    var valor_redondeado = $.fn.redondear_a_centena(total_factura);

    $("#total_factura").text(
      "$ " + new Intl.NumberFormat("de-DE").format(valor_redondeado)
    );

    // input hidden
    $("#valor_total_factura").val(total_factura);

    valor_ajuste_al_peso = valor_redondeado - total_factura;

    $("#lbl_ajuste_al_peso").text(
      "$ " + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso)
    );

    if ($("#manejar_propinas").val() == 1) {
      $.fn.calcular_valor_a_pagar_propina(total_factura);

      $.fn.calcular_totales_propina();
    }

    if (
      $("#manejar_datafono").val() == 1 &&
      $("#calcular_comision_datafono").is(":checked")
    ) {
      $.fn.calcular_valor_a_pagar_datafono(total_factura);

      $.fn.calcular_totales_datafono();
    }

    $("#valor_sub_total_factura").val(total_factura);
    $("#lbl_sub_total_factura").text(
      "$ " + new Intl.NumberFormat("de-DE").format(total_factura)
    );
  }

  var valor_actual, elemento_modificar, elemento_padre;

  // Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
  $(document).on("dblclick", ".elemento_modificar", function () {
    if (
      $("#bloqueo_cambiar_precio_unitario").val() == 1 &&
      $(this).attr("id") == "elemento_modificar_precio_unitario"
    ) {
      var fila = $(this).closest("tr");

      var producto = productos.find(
        (item) => item.id === parseInt(fila.find(".inv_producto_id").text())
      );

      if (
        producto.inv_grupo_id !=
        $("#categoria_id_paquetes_con_materiales_ocultos").val()
      ) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text("No tiene permiso para modificar precios.");
        return false;
      }
    }

    $("#popup_alerta").hide();

    elemento_modificar = $(this);

    elemento_padre = elemento_modificar.parent();

    valor_actual = $(this).html();

    elemento_modificar.hide();

    elemento_modificar.after(
      '<input type="text" name="valor_nuevo" id="valor_nuevo" style="display:inline;"> '
    );

    document.getElementById("valor_nuevo").value = valor_actual;
    document.getElementById("valor_nuevo").select();
  });

  // Si la caja de texto pierde el foco
  $(document).on("blur", "#valor_nuevo", function (event) {
    var x = event.which || event.keyCode; // Capturar la tecla presionada
    if (x != 13) {
      // 13 = Tecla Enter
      elemento_padre.find("#valor_nuevo").remove();
      elemento_modificar.show();
    }
  });

  // Al presiona teclas en la caja de texto
  $(document).on("keyup", "#valor_nuevo", function (event) {
    var x = event.which || event.keyCode; // Capturar la tecla presionada

    // Abortar la edición
    if (x == 27) {
      // 27 = ESC
      elemento_padre.find("#valor_nuevo").remove();
      elemento_modificar.show();
      return false;
    }

    // Guardar
    if (x == 13) {
      // 13 = ENTER
      var fila = $(this).closest("tr");
      guardar_valor_nuevo(fila);
    }
  });

  $("#btn_listar_items").click(function (event) {
    $("#myModal").modal({ keyboard: true });
    $(".btn_edit_modal").hide();
    $(".btn_edit_modal").hide();
    $("#myTable_filter").find("input").css("border", "3px double red");
    $("#myTable_filter").find("input").focus();
  });

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
    }); /**/
  });

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

        fila.find("td").eq(7).text("Anulado");
        fila.find(".btn_modificar_factura").hide();
        fila.find(".btn_anular_factura").hide();
        alert("Documento anulado correctamente.");
      });
    } else {
      return false;
    }
  });

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

  function guardar_valor_nuevo(fila) {
    var valor_nuevo = document.getElementById("valor_nuevo").value;

    // Si no cambió el valor_nuevo, no pasa nada
    if (valor_nuevo == valor_actual) {
      return false;
    }

    elemento_modificar.html(valor_nuevo);
    elemento_modificar.show();

    var producto = productos.find(
      (item) => item.id === parseInt(fila.find(".inv_producto_id").text())
    );
    costo_unitario = producto.costo_promedio;

    calcular_precio_total_lbl(fila);

    if (!validar_venta_menor_costo()) {
      elemento_modificar.html(valor_actual);
    }

    $("#inv_producto_id").focus();

    calcular_precio_total_lbl(fila);
    calcular_totales();

    reset_efectivo_recibido();
    $("#total_valor_total").actualizar_medio_recaudo();

    elemento_padre.find("#valor_nuevo").remove();
  }

  function calcular_precio_total_lbl(fila) {
    tasa_descuento = parseFloat(fila.find(".tasa_descuento").text());
    cantidad = parseFloat(fila.find(".elemento_modificar").eq(0).text());
    precio_unitario = parseFloat(fila.find(".elemento_modificar").eq(1).text());

    var tasa_impuesto = parseFloat(fila.find(".lbl_tasa_impuesto").text());

    valor_unitario_descuento = (precio_unitario * tasa_descuento) / 100;
    valor_total_descuento = valor_unitario_descuento * cantidad;

    var precio_venta = precio_unitario - valor_unitario_descuento;
    base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

    precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

    fila.find(".precio_unitario").text(precio_unitario);

    fila.find(".base_impuesto").text(base_impuesto_unitario);

    fila.find(".valor_impuesto").text(precio_venta - base_impuesto_unitario);

    fila.find(".cantidad").text(cantidad);

    fila.find(".precio_total").text(precio_total);

    fila.find(".base_impuesto_total").text(base_impuesto_unitario * cantidad);

    fila.find(".valor_total_descuento").text(valor_total_descuento);

    fila
      .find(".lbl_valor_total_descuento")
      .text(
        new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2))
      );

    fila
      .find(".lbl_precio_total")
      .text(new Intl.NumberFormat("de-DE").format(precio_total.toFixed(2)));
  }

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

      $("#inv_producto_id").focus();
    });
  });
});

$.fn.set_catalogos = function (pdv_id) {
  $("#contenido_modal2").html("Cargando recursos... por favor espere.");

  $("#contenido_modal2").attr("style", "text-align: center;color: #42A3DC;");
  $("#myModal2 .modal-content").attr(
    "style",
    "background: transparent;border: 0px solid;box-shadow: none;"
  );
  $("#div_spin2").fadeIn();

  $("#myModal2").modal({ backdrop: "static" });

  $("#myModal2 .modal-title").text("");

  $("#myModal2 .btn_edit_modal").hide();
  $("#myModal2 .btn-danger").hide();
  $("#myModal2 .btn_save_modal").hide();
  $("#myModal2 .close").hide();

  $.get(url_raiz + "/ventas_pos_set_catalogos" + "/" + pdv_id).done(function (
    datos
  ) {
    redondear_centena = datos.redondear_centena;

    productos = datos.productos;
    precios = datos.precios;
    descuentos = datos.descuentos;
    clientes = datos.clientes;
    cliente_default = datos.cliente_default;
    forma_pago_default = datos.forma_pago_default;
    fecha_vencimiento_default = datos.fecha_vencimiento_default;

    $("#myModal2 .modal-content").removeAttr("style");
    $("#contenido_modal2").removeAttr("style");
    $("#myModal2 .close").show();
    $("#myModal2").modal("hide");
  });
};
