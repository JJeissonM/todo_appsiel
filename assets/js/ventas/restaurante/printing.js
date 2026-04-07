function enviar_impresion( doc_encabezado )
{
    var metodo_impresion = $('#metodo_impresion_pedido_restaurante').val() || 'normal';
    if ( $('#mostrar_mensaje_impresion_delegada').val() == 1 ) { 
        $('.btn_vendedor').first().focus();
        Swal.fire({
            icon: 'info',
            title: 'Muy bien!',
            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. RECUERDA: Debes informar al responsable para su impresion.'
        });
    }else{

        if ( metodo_impresion == 'apm' ) {
            Swal.fire({
                title: 'Enviando a APM...',
                text: 'Por favor espere.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                onOpen: function () {
                    Swal.showLoading();
                }
            });

            apm_imprimir_pedido_restaurante( doc_encabezado )
                .then(function (response) {
                    Swal.close();
                    Swal.fire({
                        icon: 'info',
                        title: 'Muy bien!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + '. Solicitud enviada a APM. ' + (response.CopyLabel || '')
                    });
                })
                .catch(function (apm_error) {
                    Swal.close();
                    var errorMessage = apm_error && apm_error.ErrorMessage ? apm_error.ErrorMessage : 'Error desconocido al imprimir con APM.';
                    var pendingMessage = apm_error && apm_error.QueueJobId ? ' Quedo pendiente en la cola APM para reimpresion manual.' : '';
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente, pero no se pudo enviar a APM: ' + errorMessage + pendingMessage
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
            $('#popup_alerta_success').text('Enviando impresion a la cocina... Por favor, espere!');

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
                            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Impresion enviada.'
                        }); 
                        
                    }else{
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Pero No se pudo enviar la impresion a la cocina!' + "\n" + response.message
                        });
                    }
                },
                error: function( response, status, jqXHR ) { 
                    $('#popup_alerta_success').hide();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Pero No se pudo enviar la impresion a la cocina!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
                    });
                }
            });

        }else{

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

    restablecer_btn_guardar_factura();
    reset_datos_pedido();
    resetear_ventana();
}

function apm_imprimir_pedido_restaurante( doc_encabezado )
{
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.enqueuePrintJob !== 'function' ) {
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

    return window.APM_CLIENT.enqueuePrintJob({
        payload: payload,
        documentMeta: crear_meta_documento_apm_pedido_restaurante(doc_encabezado),
        timeoutMs: 30000
    });
}

function crear_fecha_hora_local_apm()
{
    var ahora = new Date();
    var pad = function (valor) {
        return String(valor).padStart(2, '0');
    };
    var timezoneOffset = -ahora.getTimezoneOffset();
    var offsetSign = timezoneOffset >= 0 ? '+' : '-';
    var offsetHours = pad(Math.floor(Math.abs(timezoneOffset) / 60));
    var offsetMinutes = pad(Math.abs(timezoneOffset) % 60);

    return ahora.getFullYear() +
        '-' + pad(ahora.getMonth() + 1) +
        '-' + pad(ahora.getDate()) +
        'T' + pad(ahora.getHours()) +
        ':' + pad(ahora.getMinutes()) +
        ':' + pad(ahora.getSeconds()) +
        offsetSign + offsetHours + ':' + offsetMinutes;
}

function crear_meta_documento_apm_pedido_restaurante( doc_encabezado )
{
    var consecutivo = parseInt(doc_encabezado.consecutivo || doc_encabezado.doc_encabezado_consecutivo || $('#lbl_consecutivo_doc_encabezado').val() || '0', 10) || 0;

    return {
        core_empresa_id: parseInt(doc_encabezado.core_empresa_id || $('#core_empresa_id').val() || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt(doc_encabezado.core_tipo_transaccion_id || $('#core_tipo_transaccion_id').val() || '60', 10) || 60,
        core_tipo_doc_app_id: parseInt(doc_encabezado.core_tipo_doc_app_id || $('#core_tipo_doc_app_id').val() || '0', 10) || 0,
        consecutivo: consecutivo,
        document_type: 'comanda',
        document_label: doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('Pedido restaurante ' + consecutivo)
    };
}

function crear_payload_apm_comanda( doc_encabezado )
{
    var fecha_hora_actual = crear_fecha_hora_local_apm();
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
                'Seller': doc_encabezado.doc_encabezado_vendedor_descripcion || '',
                'COPY': 'COPIA # 1'
            },
            'company': company,
            'order': {
                'COPY': 'COPIA # 1',
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Table': mesa || '',
                'Waiter': mesero || '',
                'Date': fecha_hora_actual,
                'RestaurantName': station_id || '',
                'Items': items,
                'GeneratedDate': fecha_hora_actual,
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
