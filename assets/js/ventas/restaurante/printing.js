
function enviar_impresion( doc_encabezado )
{
    var metodo_impresion = $('#metodo_impresion_pedido_restaurante').val() || 'normal';
    if ( $('#mostrar_mensaje_impresion_delegada').val() == 1 ) { 
        $('.btn_vendedor').first().focus();
        Swal.fire({
            icon: 'info',
            title: 'Muy bien!',
            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. RECUERDA: Debes informar al responsable para su impresión.'
        });
    }else{

        if ( metodo_impresion == 'apm' ) {
            Swal.fire({
                title: 'Enviando a APM...',
                text: 'Por favor espere.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: function () {
                    Swal.showLoading();
                }
            });

            apm_imprimir_pedido_restaurante( doc_encabezado )
                .then(function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'info',
                        title: 'Muy bien!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + '. Solicitud enviada a APM.'
                    });
                })
                .catch(function (apm_error) {
                    Swal.close();
                    var errorMessage = apm_error && apm_error.ErrorMessage ? apm_error.ErrorMessage : 'Error desconocido al imprimir con APM.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente, pero no se pudo enviar a APM: ' + errorMessage
                    });
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

function apm_imprimir_pedido_restaurante( doc_encabezado )
{
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.sendAndWait !== 'function' ) {
        return Promise.reject({
            Status: 'ERROR',
            ErrorMessage: 'APM no esta disponible en este navegador.'
        });
    }

    var payload = crear_payload_apm_comanda( doc_encabezado );
    if ( !payload.PrinterId ) {
        return Promise.reject({
            Status: 'ERROR',
            ErrorMessage: 'No hay impresora APM configurada para pedidos de restaurante.'
        });
    }

    return window.APM_CLIENT.sendAndWait(payload, 30000);
}

function crear_payload_apm_comanda( doc_encabezado )
{
    var printer_id = $('#apm_printer_id_pedidos_restaurante').val();
    if ( typeof printer_id === 'undefined' || printer_id === '' ) {
        printer_id = $('#printer_ip').val();
    }
    if ( typeof printer_id === 'undefined' || printer_id === '' ) {
        printer_id = $('#impresora_cocina_por_defecto').val();
    }

    var station_id = $('#pdv_label').val();
    if ( typeof station_id === 'undefined' || station_id === '' ) {
        station_id = $('#pdv_id').val();
    }

    var items = [];
    $('.linea_registro').each(function(){
        var notes = '';
        var notes_field = $(this).find('.lbl_observaciones');
        if ( notes_field.length ) {
            notes = notes_field.text();
        }

        items.push({
            'Name': $(this).find('.lbl_producto_descripcion').text(),
            'Qty': (parseInt($(this).find('.cantidad').text(), 10) || 1),
            'Notes': notes
        });
    });

    var mesa = $('#lbl_mesa_seleccionada').text();
    var mesero = $('#lbl_vendedor_mesero').text();
    var detalle = doc_encabezado.doc_encabezado_descripcion || $('#descripcion').val() || '';

    var cliente_nombre = doc_encabezado.doc_encabezado_tercero_nombre_completo || $('#cliente_input').val() || '';

    var empresa = doc_encabezado.empresa || {};
    var company = {
        'Name': empresa.descripcion || empresa.razon_social || station_id || '',
        'Nit': empresa.numero_identificacion || empresa.nit || '',
        'Address': empresa.direccion1 || empresa.direccion || '',
        'Phone': empresa.telefono1 || empresa.telefono || ''
    };

    return {
        'JobId': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('PEDIDO-' + Date.now()),
        'StationId': station_id || '',
        'PrinterId': printer_id || '',
        'DocumentType': 'comanda',
        'Document': {
            'header': {
                'Transaction': doc_encabezado.doc_encabezado_documento_transaccion_descripcion || '',
                'Date': doc_encabezado.doc_encabezado_fecha || '',
                'Time': doc_encabezado.doc_encabezado_hora_creacion || '',
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Customer': cliente_nombre,
                'Seller': doc_encabezado.doc_encabezado_vendedor_descripcion || ''
            },
            'company': company,
            'order': {
                'COPY': 'ORIGINAL',
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Table': mesa || '',
                'Waiter': mesero || '',
                'Date': new Date().toISOString(),
                'RestaurantName': station_id || '',
                'Items': items,
                'GeneratedDate': new Date().toISOString(),
                'CreatedBy': doc_encabezado.doc_encabezado_vendedor_descripcion || ''
            },
            'Detail': detalle,
            'customer': {
                'Name': cliente_nombre
            },
            'labels': doc_encabezado.etiquetas || {},
            'resolution': doc_encabezado.resolucion || {}
        }
    };
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
                    'quantity': $(this).find('.cantidad').text(),
                    'unit_price': $(this).find('.precio_unitario').text(),
                    'total_amount': $(this).find('.precio_total').text()
                }
        i++
    });
    
    json.lines = lines

    return json
}

function restablecer_btn_guardar_factura()
{
    var btn = $('#btn_guardar_factura_no');
    if ( btn.length ) {
        btn.find('i').first().attr('class','fa fa-check');
        btn.prop('id','btn_guardar_factura');
    }

    $('#btn_guardar_factura').find('i').first().attr('class','fa fa-check');
    $('#btn_guardar_factura').removeAttr('disabled');
}
