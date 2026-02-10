
function print_comanda() {
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
        var unitPrice = parseFloat( $(this).find('.elemento_modificar').eq(1).text() || 0 );
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

    var total_factura = parseFloat( $('.lbl_total_factura').first().text().replace(/[^0-9\.-]+/g, '') || subtotal );

    return {
        'JobId': $('.lbl_consecutivo_doc_encabezado').text() || ('PEDIDO-' + Date.now()),
        'StationId': station_id,
        'PrinterId': printer_id,
        'DocumentType': 'ticket_venta',
        'Document': {
            'header': {
                'Number': $('.lbl_consecutivo_doc_encabezado').text() || '',
                'Date': $('#lbl_fecha').text() || '',
                'Customer': $('.lbl_cliente_descripcion').text() || ''
            },
            'company': {
                'Name': station_id,
                'Nit': '',
                'Address': '',
                'Phone': ''
            },
            'sale': {
                'Number': $('.lbl_consecutivo_doc_encabezado').text() || '',
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
