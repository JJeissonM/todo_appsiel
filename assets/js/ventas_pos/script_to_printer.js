
    //Do printing...
    function print_comanda( doc_encabezado ) {

        var url = url_raiz + '/sys_send_printing_to_server'

        var data = crear_string_json_para_envio_servidor_impresion( doc_encabezado )
        
        data.printer_ip = $('#impresora_cocina_por_defecto').val()
        data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

        $('#popup_alerta_success').show();
        $('#popup_alerta_success').css('background-color', 'blue');
        $('#popup_alerta_success').text('Enviando impresión a la cocina... Por favor, espere!');

        $.ajax({
            url: url,
            data: data,
            type: 'GET',
            success: function( response, status, jqXHR ) {
                $('#popup_alerta_success').hide();
                Swal.fire({
                    icon: 'info',
                    title: 'Muy bien!',
                    text: 'Factura ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creada correctamente. Impresión enviada.'
                }); 
            },
            error: function( response, status, jqXHR ) { 
                $('#popup_alerta_success').hide();
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'No se pudo enviar la impresión a la cocina!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
                });
            }
        });
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