
function enviar_impresion( doc_encabezado )
{
    if ( doc_encabezado == 'prefactura' ) {
        $("#msj_ventana_impresion_abierta").show();
        ventana_imprimir();
        return;
    }

    var metodo_comanda = $('#metodo_impresion_pedido_restaurante').val() || 'normal';
    var metodo_factura = $('#metodo_impresion_factura_pos').val() || 'normal';

    // Imprimir siempre en cocina
    if ( metodo_comanda == 'apm' ) {
        if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 1 ) {
            print_comanda_apm(doc_encabezado);
        }
    } else if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 1 ) {
        print_comanda(doc_encabezado);
    }

    // Preguntar para imprimir en cocina (alert)
    if ( $("#enviar_impresion_directamente_a_la_impresora").val() == 2 ) {
        if (confirm("Â¿Quiere imprimir COMANDA en la cocina?") == true) {
            if ( metodo_comanda == 'apm' ) {
                print_comanda_apm(doc_encabezado);
            } else {
                print_comanda(doc_encabezado);
            }
        }
    }

    switch ( $("#imprimir_factura_automaticamente").val() ) {    
        case '1':
            if ( metodo_factura == 'apm' ) {
                print_factura_apm(doc_encabezado);
            } else {
                print_factura(doc_encabezado);
            }
            break;
        case '2':
            if (confirm("Â¿Quiere enviar la FACTURA a la impresora principal?") == true) {
                if ( metodo_factura == 'apm' ) {
                    print_factura_apm(doc_encabezado);
                } else {
                    print_factura(doc_encabezado);
                }
            }
            break;
        default:
            // Case 0 o null
            $("#msj_ventana_impresion_abierta").show();
            ventana_imprimir();
            break;
            break;
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

function print_comanda_apm( doc_encabezado )
{
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.send !== 'function' ) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'APM no esta disponible en este navegador.'
        });
        return false;
    }

    var payload = crear_payload_apm_comanda( doc_encabezado );
    window.APM_CLIENT.send(payload);
    return true;
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
            'Qty': $(this).find('.cantidad').text(),
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
                'Seller': doc_encabezado.doc_encabezado_vendedor_descripcion || ''
            },
            'order': {
                'COPY': 'ORIGINAL',
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
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.send !== 'function' ) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: 'APM no esta disponible en este navegador.'
        });
        return false;
    }

    var payload = crear_payload_apm_factura( doc_encabezado );
    window.APM_CLIENT.send(payload);
    return true;
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
        'Name': empresa.descripcion || empresa.razon_social || '',
        'Nit': empresa.numero_identificacion || empresa.nit || '',
        'Address': empresa.direccion1 || empresa.direccion || '',
        'Phone': empresa.telefono1 || empresa.telefono || ''
    };

    var items = [];
    var subtotal = 0;
    $('.linea_registro').each(function(){
        var qty = parseFloat( $(this).find('.cantidad').text() || 0 );
        var unitPrice = parseFloat( $(this).find('.precio_unitario').text() || 0 );
        var total = parseFloat( $(this).find('.precio_total').text() || 0 );

        subtotal += total;

        items.push({
            'Name': $(this).find('.lbl_producto_descripcion').text(),
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
        'JobId': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || ('FACTURA-' + Date.now()),
        'StationId': station_id,
        'PrinterId': printer_id,
        'DocumentType': 'ticket_venta',
        'Document': {
            'company': company,
            'customer': {
                'Name': cliente.descripcion || doc_encabezado.doc_encabezado_tercero_nombre_completo || '',
                'Nit': cliente.numero_identificacion || '',
                'Address': cliente.direccion1 || '',
                'Phone': cliente.telefono1 || '',
                'Email': cliente.email || '',
                'City': cliente.descripcion_ciudad || '',
                'IdType': cliente.descripcion_tipo_documento_identidad || ''
            },
            'seller': {
                'Name': doc_encabezado.doc_encabezado_vendedor_descripcion || ''
            },
            'sale': {
                'Number': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || '',
                'Date': new Date().toISOString(),
                'Items': items,
                'Subtotal': subtotal,
                'IVA': iva,
                'Total': total_factura
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
