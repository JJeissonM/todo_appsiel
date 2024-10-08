
function generar_string_celdas_edit(linea){
    
    var celdas = [];
    var num_celda = 0;

    celdas[num_celda] = '<td style="display: none;"><div class="inv_producto_id">' + linea.inv_producto_id + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="precio_unitario">' + linea.precio_unitario + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto">' + linea.base_impuesto_unitario + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="tasa_impuesto">' + linea.tasa_impuesto + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="valor_impuesto">' + linea.valor_impuesto_unitario + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto_total">' + linea.base_impuesto_unitario * linea.cantidad + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="cantidad">' + linea.cantidad + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="precio_total">' + linea.precio_total + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="tasa_descuento">' + linea.tasa_descuento + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td style="display: none;"><div class="valor_total_descuento">' + linea.valor_total_descuento + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td> &nbsp; </td>';

    num_celda++;

    var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

    celdas[num_celda] = '<td> &nbsp;&nbsp; <div class="lbl_producto_descripcion" style="display: inline;"> ' + linea.lbl_producto_descripcion + ' </div> </td>';

    num_celda++;

    celdas[num_celda] = '<td> ' + linea.cantidad + ' </td>';

    num_celda++;

    celdas[num_celda] = '<td> <div class="lbl_precio_unitario" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(linea.precio_unitario) + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td>' + linea.tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format(linea.valor_total_descuento.toFixed(0)) + '</div> ) </td>';

    num_celda++;

    celdas[num_celda] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">' + linea.tasa_impuesto + '</div></td>';

    num_celda++;

    celdas[num_celda] = '<td> <div class="lbl_precio_total" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(linea.precio_total.toFixed(0)) + ' </div> </td> <td> &nbsp; </td>';

    var cantidad_celdas = celdas.length;
    var string_celdas = '';
    for (var i = 0; i < cantidad_celdas; i++) {
        string_celdas = string_celdas + celdas[i];
    }

    return string_celdas;
};


function reset_pedidos_mesero_para_una_mesa()
{
    $('#div_pedidos_mesero_para_una_mesa').text( '' );
}

$(document).ready(function () {

    $(document).on('click', '.btn_pedido_mesero_para_una_mesa', function () {
        
        $("#div_cargando").show();
        $('#ingreso_registros').find('tbody').html('');
        $('#numero_lineas').text('0');

        mostrar_botones_productos();

        var url = url_raiz + "/" + "vtas_cargar_datos_editar_pedido" + "/" + $(this).attr('data-pedido_id');

        $.get(url, function (un_pedido) {
            
            $("#div_cargando").hide();
            
            var lineas_registros = un_pedido.lineas_registro;

            lineas_registros.forEach(linea => {				
                var string_fila = generar_string_celdas_edit( linea );
                $('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="' + linea.numero_linea + '">' + string_fila + '</tr>');
                $("#btn_"+linea.inv_producto_id).hide();
			});

            // Se calculan los totales
            calcular_totales();           
        
            $("#div_ingreso_registros").find('h5').html('Ingreso de productos<br><span style="color:red;">Modificar pedido: '+un_pedido.pedido_label+ ', ' +un_pedido.mesa_label+'</span>');
            $('#pedido_id').val(un_pedido.pedido_id);
            
            $('#descripcion').val(un_pedido.doc_encabezado_descripcion);
            
            $('#numero_lineas').text(un_pedido.numero_lineas);

            hay_productos = un_pedido.numero_lineas;
            numero_lineas = un_pedido.numero_lineas;
            
            $('#btn_guardar_factura').hide();
            //$('#btn_modificar_pedido').show();
            $('#btn_anular_pedido').attr('data-pedido_label',un_pedido.pedido_label+ ', ' +un_pedido.mesa_label);
            $('#btn_anular_pedido').show();

            $('#btn_crear_nuevo_pedido').show();
            
            $('#btn_imprimir_pedido').attr('data-pedido_label',un_pedido.pedido_label+ ', ' +un_pedido.mesa_label);
            $('#btn_imprimir_pedido').attr('data-doc_encabezado_documento_transaccion_descripcion',un_pedido.doc_encabezado_documento_transaccion_descripcion);
            $('#btn_imprimir_pedido').attr('data-doc_encabezado_documento_transaccion_prefijo_consecutivo',un_pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo);
            $('#btn_imprimir_pedido').show();
            
        });

    });
});