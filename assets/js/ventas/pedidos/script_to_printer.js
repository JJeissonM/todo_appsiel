$(document).ready(function() {

    $("#btn_imprimir_en_cocina").on('click',function(event){
        event.preventDefault();

        var metodo_impresion_pedido = $('#metodo_impresion_pedido_ventas').val() || 'normal';
        if ( metodo_impresion_pedido == 'apm' ) {
            print_comanda_apm()
                .then(function (response) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Muy bien!',
                        text: 'Pedido enviado a la impresora por APM. ' + (response.CopyLabel || '')
                    });
                })
                .catch(function (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error && error.ErrorMessage ? error.ErrorMessage : 'No fue posible enviar el pedido a APM.'
                    });
                });
        } else {
            if ( typeof ventana_imprimir === 'function' ) {
                ventana_imprimir();
            } else {
                window.print();
            }
        }
    });
    
});

function print_comanda_apm() {
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.enqueuePrintJob !== 'function' ) {
        return Promise.reject({
            Status: 'ERROR',
            ErrorMessage: 'APM no esta disponible en este navegador.'
        });
    }

    return window.APM_CLIENT.enqueuePrintJob({
        payload: crear_payload_apm_pedido_ventas(),
        documentMeta: crear_meta_apm_pedido_ventas(),
        timeoutMs: 30000
    });
}

function crear_meta_apm_pedido_ventas()
{
    var consecutivo = parseInt(($('#lbl_consecutivo_doc_encabezado').val() || $('#lbl_consecutivo_doc_encabezado').text() || '0'), 10) || 0;

    return {
        core_empresa_id: parseInt($('#core_empresa_id').val() || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt($('#core_tipo_transaccion_id').val() || '42', 10) || 42,
        core_tipo_doc_app_id: parseInt($('#core_tipo_doc_app_id').val() || $('#lbl_core_tipo_doc_app_id').val() || '0', 10) || 0,
        consecutivo: consecutivo,
        document_type: 'ticket_venta',
        document_label: ($('#lbl_consecutivo_doc_encabezado').val() || $('#lbl_consecutivo_doc_encabezado').text() || ('Pedido ' + consecutivo))
    };
}

function crear_payload_apm_pedido_ventas()
{
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

    var station_id = $('#pdv_label').val() || $('#pdv_id').val() || '';
    var printer_id = $('#apm_printer_id_pedidos_ventas').val() || $('#impresora_cocina_por_defecto').val() || '';

    var total_label = $('#vlr_total_factura').text() || $('#lbl_total_factura').val() || $('#lbl_total_factura').text() || '';
    var total_factura = parseFloat( total_label.replace(/[^0-9\.-]+/g, '') || subtotal );

    return {
        'JobId': $('#lbl_consecutivo_doc_encabezado').val() || $('#lbl_consecutivo_doc_encabezado').text() || ('PEDIDO-' + Date.now()),
        'StationId': station_id,
        'PrinterId': printer_id,
        'DocumentType': 'ticket_venta',
        'Document': {
            'header': {
                'Number': $('#lbl_consecutivo_doc_encabezado').val() || $('#lbl_consecutivo_doc_encabezado').text() || '',
                'Date': $('#lbl_fecha').val() || $('#lbl_fecha').text() || '',
                'Customer': $('#lbl_cliente_descripcion').val() || $('#lbl_cliente_descripcion').text() || '',
                'COPY': 'COPIA # 1'
            },
            'company': {
                'Name': station_id,
                'Nit': '',
                'Address': '',
                'Phone': ''
            },
            'sale': {
                'Number': $('#lbl_consecutivo_doc_encabezado').val() || $('#lbl_consecutivo_doc_encabezado').text() || '',
                'Date': new Date().toISOString(),
                'Items': items,
                'Subtotal': subtotal,
                'IVA': 0,
                'Total': total_factura,
                'COPY': 'COPIA # 1'
            },
            'footer': ['Pedido de ventas']
        }
    };
}
