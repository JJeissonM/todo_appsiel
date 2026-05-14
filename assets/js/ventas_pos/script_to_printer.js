
function enviar_impresion( doc_encabezado )
{
    if ( doc_encabezado == 'prefactura' ) {
        $("#msj_ventana_impresion_abierta").show();
        var prefactura_impresa = ventana_imprimir();
        if (!prefactura_impresa) {
            pos_notificar_popup_bloqueado();
        }
        return;
    }

    var metodo_comanda = $('#metodo_impresion_pedido_restaurante').val() || 'normal';
    var metodo_factura = $('#metodo_impresion_factura_pos').val() || 'normal';
    var apm_jobs = [];
    var mostrar_previsualizacion_factura = false;

    // Imprimir siempre en cocina
    if ( metodo_comanda == 'apm' ) {
        if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 1 ) {
            apm_jobs.push({
                label: 'Comanda',
                promise: print_comanda_apm(doc_encabezado)
            });
        }
    } else if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 1 ) {
        print_comanda(doc_encabezado);
    }

    // Preguntar para imprimir en cocina (alert)
    if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 2 ) {
        if (confirm("¿Quiere imprimir COMANDA en la cocina?") == true) {
            if ( metodo_comanda == 'apm' ) {
                apm_jobs.push({
                    label: 'Comanda',
                    promise: print_comanda_apm(doc_encabezado)
                });
            } else {
                print_comanda(doc_encabezado);
            }
        }
    }

    switch ( $("#imprimir_factura_automaticamente").val() ) {    
        case '1':
            if ( metodo_factura == 'apm' ) {
                mostrar_previsualizacion_factura = true;
                apm_jobs.push({
                    label: 'Factura POS',
                    promise: print_factura_apm(doc_encabezado)
                });
            } else {
                print_factura(doc_encabezado);
            }
            break;
        case '2':
            if (confirm("¿Quiere enviar la FACTURA a la impresora principal?") == true) {
                if ( metodo_factura == 'apm' ) {
                    mostrar_previsualizacion_factura = true;
                    apm_jobs.push({
                        label: 'Factura POS',
                        promise: print_factura_apm(doc_encabezado)
                    });
                } else {
                    print_factura(doc_encabezado);
                }
            }
            break;
        default:
            // Case 0 o null
            if ( metodo_factura != 'apm' ) {
                $("#msj_ventana_impresion_abierta").show();
                var factura_impresa = ventana_imprimir();
                if (!factura_impresa) {
                    pos_notificar_popup_bloqueado();
                }
            } else {
                mostrar_previsualizacion_factura = true;
                apm_jobs.push({
                    label: 'Factura POS',
                    promise: print_factura_apm(doc_encabezado)
                });
            }
            break;
    }

    if ( mostrar_previsualizacion_factura ) {
        var preview_abierta = ventana_imprimir(false);
        if (!preview_abierta) {
            pos_notificar_popup_bloqueado();
        }
    }

    if ( apm_jobs.length ) {
        pos_mostrar_modal_apm(apm_jobs, doc_encabezado);
    }

    if ( doc_encabezado != 'prefactura' ) {
        resetear_ventana();
    }    

    if ($("#action").val() != "create") {
        location.href =
            url_raiz +
            "/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=" +
            $("#pdv_id").val() +
            "&action=create";
    }
}

function pos_mostrar_modal_apm(apm_jobs, doc_encabezado)
{
    var documento = '';
    if (doc_encabezado && doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo) {
        documento = doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo;
    }

    if (window.focus) {
        window.focus();
    }

    if (window.Swal) {
        Swal.fire({
            title: 'Impresion APM',
            html: '<p>Enviando ' + apm_jobs.length + ' trabajo(s) a Appsiel Print Manager...</p><p><b>' + documento + '</b></p>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            onOpen: function () {
                Swal.showLoading();
            }
        });
    } else {
        $('#popup_alerta_success').show();
        $('#popup_alerta_success').css('background-color', 'black');
        $('#popup_alerta_success').text('Enviando impresion por APM... Por favor, espere!');
    }

    Promise.all(apm_jobs.map(function (job) {
        return Promise.resolve(job.promise)
            .then(function (response) {
                return {
                    label: job.label,
                    ok: true,
                    response: response
                };
            })
            .catch(function (error) {
                return {
                    label: job.label,
                    ok: false,
                    error: error
                };
            });
    })).then(function (results) {
        var failed = results.filter(function (result) {
            return !result.ok;
        });

        $('#popup_alerta_success').hide();

        if (!window.Swal) {
            return;
        }

        if (failed.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Impresion APM con novedades',
                html: pos_build_apm_result_html(results)
            });
            return;
        }

        Swal.fire({
            icon: 'success',
            title: 'Impresion enviada a APM',
            html: pos_build_apm_result_html(results),
            timer: 2500,
            showConfirmButton: false
        });
    });
}

function pos_build_apm_result_html(results)
{
    var html = '<div style="text-align:left;">';

    results.forEach(function (result) {
        if (result.ok) {
            html += '<p><b>' + result.label + ':</b> enviada correctamente.</p>';
            return;
        }

        var message = 'No fue posible enviar la impresion.';
        if (result.error && result.error.ErrorMessage) {
            message = result.error.ErrorMessage;
        }

        html += '<p><b>' + result.label + ':</b> ' + message + '</p>';
    });

    return html + '</div>';
}

function pos_notificar_popup_bloqueado()
{
    Swal.fire({
        icon: 'warning',
        title: 'Ventana emergente bloqueada',
        text: 'La factura se guardo correctamente, pero el navegador bloqueo la ventana de impresion.'
    });
}

function print_comanda_apm( doc_encabezado )
{
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.enqueuePrintJob !== 'function' ) {
        return Promise.reject({
            Status: 'ERROR',
            ErrorMessage: 'APM no esta disponible en este navegador.'
        });
    }

    return window.APM_CLIENT.enqueuePrintJob({
        payload: crear_payload_apm_comanda( doc_encabezado ),
        documentMeta: crear_meta_apm_comanda( doc_encabezado ),
        timeoutMs: 30000
    });
}

//Do printing...
function print_comanda( doc_encabezado ) 
{
    var url = url_raiz + '/sys_send_printing_to_server'

    var data = crear_string_json_para_envio_servidor_impresion_comanda( doc_encabezado )
    
    data.printer_ip = $('#impresora_cocina_por_defecto').val()
    data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

    $('#popup_alerta_success').show();
    $('#popup_alerta_success').css('background-color', 'black');
    $('#popup_alerta_success').text('Enviando impresion de COMANDA a la cocina... Por favor, espere!');

    $.ajax({
        url: url,
        data: data,
        type: 'GET',
        success: function( response, status, jqXHR ) {
            $('#popup_alerta_success').hide();

            var additional_message = 'Impresion enviada.';
            var icon = 'info'
            var title = 'Muy bien!'
            if ( response.status == 'Pendiente' )
            {
                additional_message = '... ... ... Sin embargo la impresion NO fue enviada. Debe Re-imprimir la factura.';
                icon = 'warning'
                title = 'Guardado con Novedades!'
            }

            Swal.fire({
                icon: icon,
                title: title,
                text: 'Factura ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creada correctamente. ' + additional_message
            }); 
        },
        error: function( response, status, jqXHR ) { 
            $('#popup_alerta_success').hide();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'No se pudo enviar la impresion a la cocina!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
            });
        }
    });
}

function crear_payload_apm_comanda( doc_encabezado )
{
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

    var station_id = $('#pdv_label').val() || $('#pdv_id').val() || '';
    var printer_id = $('#apm_printer_id_pedidos_restaurante').val() || $('#impresora_cocina_por_defecto').val() || '';
    var cliente_nombre = doc_encabezado.doc_encabezado_tercero_nombre_completo || $('#cliente_input').val() || '';

    return {
        'JobId': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('COMANDA-' + Date.now()),
        'StationId': station_id,
        'PrinterId': printer_id,
        'DocumentType': 'comanda',
        'Document': {
            'header': {
                'Transaction': doc_encabezado.doc_encabezado_documento_transaccion_descripcion || '',
                'Date': doc_encabezado.doc_encabezado_fecha || '',
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Customer': cliente_nombre,
                'Seller': doc_encabezado.doc_encabezado_vendedor_descripcion || '',
                'COPY': 'COPIA # 1'
            },
            'order': {
                'COPY': 'COPIA # 1',
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Table': $('#lbl_mesa_seleccionada').text() || '',
                'Waiter': $('#lbl_vendedor_mesero').text() || doc_encabezado.doc_encabezado_vendedor_descripcion || '',
                'Date': new Date().toISOString(),
                'RestaurantName': station_id,
                'Items': items,
                'GeneratedDate': new Date().toISOString(),
                'CreatedBy': doc_encabezado.doc_encabezado_vendedor_descripcion || ''
            },
            'Detail': doc_encabezado.doc_encabezado_descripcion || $('#descripcion').val() || '',
            'customer': {
                'Name': cliente_nombre
            }
        }
    };
}

function crear_string_json_para_envio_servidor_impresion_comanda( doc_encabezado )
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

function print_factura_apm( doc_encabezado )
{
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.enqueuePrintJob !== 'function' ) {
        return Promise.reject({
            Status: 'ERROR',
            ErrorMessage: 'APM no esta disponible en este navegador.'
        });
    }

    return window.APM_CLIENT.enqueuePrintJob({
        payload: crear_payload_apm_factura( doc_encabezado ),
        documentMeta: crear_meta_apm_factura_pos( doc_encabezado ),
        timeoutMs: 30000
    });
}

function print_factura( doc_encabezado ) 
{
    var url = url_raiz + '/sys_send_printing_to_server'

    var data = crear_string_json_para_envio_servidor_impresion_factura( doc_encabezado )
    
    data.printer_ip = $('#impresora_principal_por_defecto').val()
    data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

    $('#popup_alerta_success').show();
    $('#popup_alerta_success').css('background-color', 'black');
    $('#popup_alerta_success').text('Enviando impresion a la impresora PRINCIPAL... Por favor, espere!');

    $.ajax({
        url: url,
        data: data,
        type: 'GET',
        success: function( response, status, jqXHR ) {
            $('#popup_alerta_success').hide();

            var additional_message = 'Impresion enviada.';
            var icon = 'info'
            var title = 'Muy bien!'
            if ( response.status == 'Pendiente' )
            {
                additional_message = '... ... ... Sin embargo la impresion NO fue enviada. Debe Re-imprimir la factura.';
                icon = 'warning'
                title = 'Guardado con Novedades!'
            }

            Swal.fire({
                icon: icon,
                title: title,
                text: 'Factura ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creada correctamente. ' + additional_message
            }); 
        },
        error: function( response, status, jqXHR ) { 
            $('#popup_alerta_success').hide();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'No se pudo enviar la impresion a la impresora PRINCIPAL!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
            });
        }
    });
}

function crear_payload_apm_factura( doc_encabezado )
{
    var empresa = doc_encabezado.empresa || {};
    var company = {
        'Name': apm_to_string(empresa.descripcion || empresa.razon_social || ''),
        'Nit': apm_to_string(empresa.numero_identificacion || empresa.nit || ''),
        'Address': apm_to_string(empresa.direccion1 || empresa.direccion || ''),
        'Phone': apm_to_string(empresa.telefono1 || empresa.telefono || '')
    };

    var items = [];
    var subtotal = 0;
    $('.linea_registro').each(function(){
        var qty = parseFloat( $(this).find('.cantidad').text() || 0 );
        var unitPrice = parseFloat( $(this).find('.precio_unitario').text() || 0 );
        var total = parseFloat( $(this).find('.precio_total').text() || 0 );

        subtotal += total;

        items.push({
            'Name': apm_to_string($(this).find('.lbl_producto_descripcion').text()),
            'Qty': qty,
            'UnitPrice': unitPrice,
            'Total': total
        });
    });

    var total_factura = parseFloat( $('#valor_total_factura').val() || subtotal );
    var iva = parseFloat( $('#valor_total_impuestos').val() || 0 );

    var station_id = $('#pdv_label').val() || $('#pdv_id').val() || '';
    var printer_id = $('#apm_printer_id_factura_pos').val() || $('#impresora_principal_por_defecto').val() || '';
    var cliente = doc_encabezado.cliente_info || {};

    return {
        'JobId': apm_to_string(doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('FACTURA-' + Date.now())),
        'StationId': apm_to_string(station_id),
        'PrinterId': apm_to_string(printer_id),
        'DocumentType': 'ticket_venta',
        'Document': {
            'company': company,
            'customer': {
                'Name': apm_to_string(cliente.descripcion || doc_encabezado.doc_encabezado_tercero_nombre_completo || ''),
                'Nit': apm_to_string(cliente.numero_identificacion || ''),
                'Address': apm_to_string(cliente.direccion1 || ''),
                'Phone': apm_to_string(cliente.telefono1 || ''),
                'Email': apm_to_string(cliente.email || ''),
                'City': apm_to_string(cliente.descripcion_ciudad || ''),
                'IdType': apm_to_string(cliente.descripcion_tipo_documento_identidad || '')
            },
            'seller': {
                'Name': apm_to_string(doc_encabezado.doc_encabezado_vendedor_descripcion || '')
            },
            'sale': {
                'Number': apm_to_string(doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ''),
                'Date': apm_to_string(new Date().toISOString()),
                'Items': items,
                'Subtotal': subtotal,
                'IVA': iva,
                'Total': total_factura,
                'COPY': 'COPIA # 1'
            },
            'totals': get_apm_totals_from_dom(),
            'taxes': get_apm_taxes_from_dom(),
            'payments': get_apm_payments_from_dom(),
            'resolution': doc_encabezado.resolucion || {},
            'labels': doc_encabezado.etiquetas || {},
            'footer': ['Gracias por su compra']
        }
    };
}

function apm_to_string(value)
{
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}

function crear_meta_apm_comanda( doc_encabezado )
{
    var consecutivo = parseInt(doc_encabezado.consecutivo || doc_encabezado.doc_encabezado_consecutivo || $('.lbl_consecutivo_doc_encabezado').text() || '0', 10) || 0;

    return {
        core_empresa_id: parseInt(doc_encabezado.core_empresa_id || $('#core_empresa_id').val() || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt(doc_encabezado.core_tipo_transaccion_id || $('#core_tipo_transaccion_id').val() || $('#url_id_transaccion').val() || '52', 10) || 52,
        core_tipo_doc_app_id: parseInt(doc_encabezado.core_tipo_doc_app_id || $('#core_tipo_doc_app_id').val() || '0', 10) || 0,
        consecutivo: consecutivo,
        document_type: 'comanda',
        document_label: doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('Comanda ' + consecutivo)
    };
}

function crear_meta_apm_factura_pos( doc_encabezado )
{
    var consecutivo = parseInt(doc_encabezado.consecutivo || doc_encabezado.doc_encabezado_consecutivo || $('.lbl_consecutivo_doc_encabezado').text() || '0', 10) || 0;

    return {
        core_empresa_id: parseInt(doc_encabezado.core_empresa_id || $('#core_empresa_id').val() || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt(doc_encabezado.core_tipo_transaccion_id || $('#core_tipo_transaccion_id').val() || $('#url_id_transaccion').val() || '47', 10) || 47,
        core_tipo_doc_app_id: parseInt(doc_encabezado.core_tipo_doc_app_id || $('#core_tipo_doc_app_id').val() || '0', 10) || 0,
        consecutivo: consecutivo,
        document_type: 'ticket_venta',
        document_label: doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('Factura POS ' + consecutivo)
    };
}
function get_apm_totals_from_dom()
{
    return {
        'Subtotal': parseFloat( $('#valor_sub_total_factura').val() || 0 ),
        'Impuestos': parseFloat( $('#valor_total_impuestos').val() || 0 ),
        'Propina': parseFloat( $('#valor_propina').val() || 0 ),
        'Datafono': parseFloat( $('#valor_datafono').val() || 0 ),
        'Ajuste': parseFloat( $('#valor_ajuste_al_peso').val() || 0 ),
        'Bolsas': parseFloat( $('#valor_total_bolsas').val() || 0 ),
        'Total': parseFloat( $('#valor_total_factura').val() || 0 ),
        'Recibido': parseFloat( $('#total_efectivo_recibido').val() || 0 ),
        'Cambio': parseFloat( $('#valor_total_cambio').val() || 0 )
    };
}

function get_apm_taxes_from_dom()
{
    var taxes = [];
    $('#tabla_resumen_impuestos tbody tr').each(function(){
        var cols = $(this).find('td');
        if ( cols.length >= 4 ) {
            taxes.push({
                'Type': cols.eq(0).text().trim(),
                'Total': cols.eq(1).text().trim(),
                'Base': cols.eq(2).text().trim(),
                'Tax': cols.eq(3).text().trim()
            });
        }
    });
    return taxes;
}

function get_apm_payments_from_dom()
{
    var payments = [];
    $('#tabla_resumen_medios_pago tbody tr').each(function(){
        var cols = $(this).find('td');
        if ( cols.length >= 3 ) {
            payments.push({
                'Method': cols.eq(0).text().trim(),
                'Account': cols.eq(1).text().trim(),
                'Amount': cols.eq(2).text().trim()
            });
        }
    });
    return payments;
}

function crear_string_json_para_envio_servidor_impresion_factura( doc_encabezado )
{    
    var json = {
        'header': {
                    'transaction_label': doc_encabezado.doc_encabezado_documento_transaccion_descripcion,
                    'date': doc_encabezado.doc_encabezado_fecha,
                    'customer_name': doc_encabezado.doc_encabezado_tercero_nombre_completo,
                    'number_label': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo,
                    'seller_label': doc_encabezado.doc_encabezado_vendedor_descripcion,
                    items_quantity: doc_encabezado.cantidad_total_productos,
                    'detail': doc_encabezado.doc_encabezado_descripcion,
                    'empresa': doc_encabezado.empresa,
                    'cliente_info': doc_encabezado.cliente_info,
                    'etiquetas': doc_encabezado.etiquetas,
                    'resolucion': doc_encabezado.resolucion
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
