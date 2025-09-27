var valor_actual, elemento_modificar, elemento_padre;

/**
 *
 * @param {*} fila
 * @returns booblean
 */
function guardar_valor_total_nuevo(fila) {

  // El input valor_total_nuevo aun esta abierto
  var valor_total_nuevo = parseFloat( document.getElementById("valor_total_nuevo").value );

  document.getElementById("valor_total_nuevo").remove();
  elemento_modificar.show();

  // Si no cambió el valor_total_nuevo, no pasa nada
  if (valor_total_nuevo == valor_actual) {
    return false;
  }

  elemento_modificar.html( '$' + new Intl.NumberFormat("de-DE").format(valor_total_nuevo.toFixed(0)) );

  calcular_cantidad(fila, valor_total_nuevo);

  $("#inv_producto_id").focus();

  calcular_totales();
  set_cantidades_ingresadas();

  reset_efectivo_recibido();
  $("#total_valor_total").actualizar_medio_recaudo();

  return true;
}

function calcular_cantidad(fila, valor_total_nuevo)
{    
  precio_unitario = parseFloat(fila.find(".elemento_modificar").eq(1).text());

  var tasa_impuesto = parseFloat(fila.find(".lbl_tasa_impuesto").text());

  valor_unitario_descuento = 0;
  valor_total_descuento = 0;

  cantidad = valor_total_nuevo / precio_unitario;

  base_impuesto_unitario = precio_unitario / (1 + tasa_impuesto / 100);

  fila.find(".base_impuesto").text(base_impuesto_unitario);
  fila.find(".base_impuesto_total").text(base_impuesto_unitario * cantidad);

  fila.find(".valor_impuesto").text(precio_unitario - base_impuesto_unitario);

  fila.find(".cantidad").text(cantidad.toFixed(2));
  var elemento = fila.find(".elemento_modificar");
  elemento.eq(0).text(cantidad.toFixed(2));

  fila.find(".valor_total_descuento").text(0);
  fila.find(".tasa_descuento").text(0);
  fila.find(".lbl_valor_total_descuento").text(0);

  fila.find(".precio_total").text(valor_total_nuevo);
  fila
    .find(".lbl_precio_total")
    .html(
      '<div class="elemento_modificar_precio_total" title="Doble click para modificar."> $' +
        new Intl.NumberFormat("de-DE").format(valor_total_nuevo.toFixed(0)) +
        "</div>"
    );
}

$(document).ready(function () {
  // Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
  $(document).on("dblclick", ".elemento_modificar_precio_total", function () {
    $("#popup_alerta").hide();

    elemento_modificar = $(this);

    elemento_padre = elemento_modificar.parent();

    valor_actual = parseFloat( $(this).html().replace('$','').replace('.','').replace('.','').replace('.','') );

    elemento_modificar.hide();

    elemento_modificar.after(
      '<input type="text" name="valor_total_nuevo" id="valor_total_nuevo" style="display:inline;"> '
    );

    document.getElementById("valor_total_nuevo").value = valor_actual;
    document.getElementById("valor_total_nuevo").select();
  });

  // Si la caja de texto pierde el foco
  $(document).on("blur", "#valor_total_nuevo", function (event) {
    var x = event.which || event.keyCode; // Capturar la tecla presionada

    var fila = $(this).closest("tr");
    guardar_valor_total_nuevo(fila);
  });

  // Al presiona teclas en la caja de texto
  $(document).on("keyup", "#valor_total_nuevo", function (event) {
    var x = event.which || event.keyCode; // Capturar la tecla presionada

    // Abortar la edición
    if (x == 27) {
      // 27 = ESC
      
      var fila = $(this).closest("tr");

      document.getElementById("valor_total_nuevo").value = valor_actual;

      fila.find(".precio_total").text(valor_actual);

      fila
        .find(".lbl_precio_total")
        .html(
          '<div class="elemento_modificar_precio_total" title="Doble click para modificar."> $' +
            new Intl.NumberFormat("de-DE").format(valor_actual.toFixed(0)) +
            "</div>"
        );
      return false;
    }

    // Guardar
    if (x == 13) {
      // 13 = ENTER
      var fila = $(this).closest("tr");
      fila.find(".btn_eliminar").focus();
    }
  });
});
