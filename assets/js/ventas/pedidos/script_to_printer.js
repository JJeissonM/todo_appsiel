
$(document).ready(function() {

    $("#btn_imprimir_en_cocina").on('click',function(event){
        event.preventDefault();

        var metodo_impresion_pedido = $('#metodo_impresion_pedido_ventas').val() || 'normal';
        if ( metodo_impresion_pedido == 'apm' ) {
            print_comanda_apm();

            Swal.fire({
                icon: 'info',
                title: 'Muy bien!',
                text: 'Pedido enviado a la impresora por APM.'
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
    if ( !window.APM_CLIENT || typeof window.APM_CLIENT.send !== 'function' ) {
        alert('APM no esta disponible en este navegador.');
        return false;
    }

    var payload = crear_payload_apm_pedido_ventas();
    window.APM_CLIENT.send(payload);
    return true;
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
                'Customer': $('#lbl_cliente_descripcion').val() || $('#lbl_cliente_descripcion').text() || ''
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
                'Total': total_factura
            },
            'footer': ['Pedido de ventas']
        }
    };
}
