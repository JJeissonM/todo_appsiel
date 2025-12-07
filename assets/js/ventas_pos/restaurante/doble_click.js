
var valor_actual, elemento_modificar, elemento_padre;


/**
 * 
 * @param {*} fila 
 * @returns booblean
 */
function guardar_valor_nuevo(fila) 
{
    var valor_nuevo = document.getElementById("valor_nuevo").value;

    // Si no cambió el valor_nuevo, no pasa nada
    if (valor_nuevo == valor_actual)
    {
      return false;
    }

    elemento_modificar.html(valor_nuevo);
    elemento_modificar.show();

    // Vaidar exitencias al modificar cantidad
    var stock_service = new StockService();
    if( elemento_modificar.attr('id') != 'elemento_modificar_precio_unitario' )
    {
        stock_service.verificarExistencia( parseInt(fila.find(".inv_producto_id").text()), $('#inv_bodega_id').val(), valor_nuevo, $('#fecha').val(), valor_actual );
        if ( !stock_service.hay_stock )
        {
            elemento_modificar.html(valor_actual);
        }
    }

    var producto = productos.find(
      (item) => item.id === parseInt(fila.find(".inv_producto_id").text())
    );
    costo_unitario = producto.costo_promedio;

    fila.find('.precio_unitario').text( valor_nuevo );

    tasa_descuento = parseFloat( fila.find('.tasa_descuento').text() );
    cantidad = parseFloat( fila.find('.quantity').val() );

    var tasa_impuesto = parseFloat( fila.find('.lbl_tasa_impuesto').text() );

    valor_unitario_descuento = valor_nuevo * tasa_descuento / 100;
    valor_total_descuento = valor_unitario_descuento * cantidad;

    var precio_venta = valor_nuevo - valor_unitario_descuento;
    base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

    fila.find('.base_impuesto').text( valor_nuevo );

    calcular_precio_total_lbl_quantity(fila);

    /*
    if (!validar_venta_menor_costo()) {
      elemento_modificar.html(valor_actual);
    }
      */

    if (!calcular_precio_total()) {
      elemento_modificar.html(valor_actual);
    }

    $("#inv_producto_id").focus();

    // Nuevamente
    calcular_precio_total_lbl_quantity(fila);
    calcular_totales_quantity();
    set_cantidades_ingresadas();

    reset_efectivo_recibido();
    $("#total_valor_total").actualizar_medio_recaudo();

    elemento_padre.find("#valor_nuevo").remove();

    return true;
  }


$(document).ready(function () {

    // Al hacer Doble Click en el elemento a modificar ( en este caso la celda de una tabla <td>)
    $(document).on("dblclick", ".elemento_modificar", function () {
        
        if ( $("#bloqueo_cambiar_precio_unitario").val() == 1 && $(this).attr("id") == "elemento_modificar_precio_unitario" ) 
        {
            var fila = $(this).closest("tr");

            var producto = productos.find( (item) => item.id === parseInt(fila.find(".inv_producto_id").text() ) );

            if ( producto.inv_grupo_id != $("#categoria_id_paquetes_con_materiales_ocultos").val() ) 
            {
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
});