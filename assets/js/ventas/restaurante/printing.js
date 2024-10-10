
function enviar_impresion( doc_encabezado )
{
    if ( $('#mostrar_mensaje_impresion_delegada').val() == 1 ) { 
        $('.btn_vendedor').first().focus();
        Swal.fire({
            icon: 'info',
            title: 'Muy bien!',
            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. RECUERDA: Debes informar al responsable para su impresión.'
        });
    }else{
        
        if ( $('#usar_servidor_de_impresion').val() == 1 ) {

            var url = url_raiz + '/sys_send_printing_to_server'

            var data = crear_string_json_para_envio_servidor_impresion( doc_encabezado )
            
            data.printer_ip = $('#printer_ip').val()
            data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

            $('#popup_alerta_success').show();
            $('#popup_alerta_success').css('background-color', 'black');
            $('#popup_alerta_success').text('Enviando impresión a la cocina... Por favor, espere!');

            $.ajax({
                url: url,
                data: data,
                type: 'GET',
                success: function( response, status, jqXHR ) {

                    $('#popup_alerta_success').hide();
                    if ( response.status == 'Completado' ) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Muy bien!',
                            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Impresión enviada.'
                        }); 
                        
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Pero No se pudo enviar la impresión a la cocina!' + "\n" + response.message
                        });
                    }
                },
                error: function( response, status, jqXHR ) { 
                    $('#popup_alerta_success').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Pero No se pudo enviar la impresión a la cocina!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
                    });
                }
            });

        }else{

            // Mostrar venta de Impresion Normal 
            $('.doc_encabezado_documento_transaccion_descripcion').append(doc_encabezado.doc_encabezado_documento_transaccion_descripcion);

            $('.doc_encabezado_documento_transaccion_prefijo_consecutivo').text(doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo);

            llenar_tabla_productos_facturados(doc_encabezado);

            ventana_imprimir();
        }
    }

    reset_componente_meseros();
    reset_componente_mesas();
    reset_pedidos_mesero_para_una_mesa();

    $('#ingreso_registros').find('tbody').html('');
    $('#descripcion').val('');

    restablecer_btn_guardar_factura()        

    reset_datos_pedido();
    
    resetear_ventana();
}


function crear_string_json_para_envio_servidor_impresion( doc_encabezado )
{
    var json = {
        'header': {
                    'transaction_label': doc_encabezado.doc_encabezado_documento_transaccion_descripcion,
                    'date': doc_encabezado.doc_encabezado_fecha,
                    'customer_name': doc_encabezado.doc_encabezado_tercero_nombre_completo,
                    'number_label': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo,
                    'seller_label': doc_encabezado.doc_encabezado_vendedor_descripcion,
                    items_quantity: doc_encabezado.cantidad_total_productos,
                    'detail': doc_encabezado.doc_encabezado_descripcion
                }
            }
    
    var lines = {}
    var i = 0;
    $('.linea_registro').each(function(){
        
        lines[i] = {
                    'item': $(this).find('.lbl_producto_descripcion').text(),
                    'quantity': $(this).find('.cantidad').text()
                }
        i++
    });
    
    json.lines = lines

    return json
}

function restablecer_btn_guardar_factura()
{
    $('#btn_guardar_factura_no').children('.fa-spinner').attr('class','fa fa-save');
    $('#btn_guardar_factura_no').prop('id','btn_guardar_factura');
                
    $('#btn_guardar_factura').removeAttr('disabled');
}