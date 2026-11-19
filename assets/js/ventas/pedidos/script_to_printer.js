$(document).ready(function() {

    var metodo_impresion_pedido = $('#metodo_impresion_pedido_ventas').val() || 'normal';
    if ( metodo_impresion_pedido == 'jspm' && window.JSPM ) {
        JSPM.JSPrintManager.auto_reconnect = true;
        JSPM.JSPrintManager.start();
    }

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
        } else if ( metodo_impresion_pedido == 'jspm' ) {
            print_comanda_jspm()
                .then(function () {
                    Swal.fire({
                        icon: 'info',
                        title: 'Muy bien!',
                        text: 'Pedido enviado a la impresora de COCINA.'
                    });
                })
                .catch(function (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: error.message || 'No fue posible enviar el pedido a JSPrintManager.'
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

function print_comanda_jspm() {
    if ( !window.JSPM || JSPM.JSPrintManager.websocket_status !== JSPM.WSStatus.Open ) {
        return Promise.reject(new Error('JSPrintManager no está conectado. Verifique que JSPM Client App esté instalado y ejecutándose, y que permita el acceso a este sitio.'));
    }

    var impresora = $('#impresora_cocina_por_defecto').val();
    if ( !impresora ) {
        return Promise.reject(new Error('Configure la impresora de cocina en Ventas POS.'));
    }

    return Promise.resolve().then(function () {
        var cpj = new JSPM.ClientPrintJob();
        cpj.clientPrinter = new JSPM.InstalledPrinter(impresora);
        cpj.binaryPrinterCommands = crear_comanda_jspm_pedido_ventas();
        return cpj.sendToClient();
    });
}

function crear_comanda_jspm_pedido_ventas() {
    var escpos = Neodynamic.JSESCPOSBuilder;
    var doc = new escpos.Document();
    var lineas = '\n  CANT.        ITEM\n______________________________\n';
    var cantidad_items = 0;

    $('.linea_registro').each(function () {
        var nombre = $(this).find('.lbl_producto_descripcion').text().trim();
        lineas += $(this).find('.cantidad').text().trim() + ' - ' + nombre.substring(0, 20) + '\n';
        for (var inicio = 20; inicio < nombre.length; inicio += 20) {
            lineas += ' ' + nombre.substring(inicio, inicio + 20) + '\n';
        }
        lineas += '\n';
        cantidad_items++;
    });

    lineas += 'Detalle: ' + $('#lbl_descripcion_doc_encabezado').val() + '\n\n';
    lineas += '# TOTAL ITEMS: ' + cantidad_items + '\n';
    lineas += 'VENTA TOTAL: ' + $('#lbl_total_factura').val() + '\n';

    return doc
        .beep(3, 2)
        .font(escpos.FontFamily.A)
        .align(escpos.TextAlignment.Center)
        .style([escpos.FontStyle.Bold])
        .size(0, 1)
        .text('PEDIDO # ' + $('#lbl_consecutivo_doc_encabezado').val())
        .text('F. ' + $('#lbl_fecha').val())
        .text($('#lbl_cliente_descripcion').val())
        .text('Atiende: ' + $('#nombre_vendedor').val())
        .font(escpos.FontFamily.B)
        .size(1, parseFloat($('#tamanio_letra_impresion_items_cocina').val()) || 0)
        .text(lineas)
        .feed(5)
        .cut()
        .generateUInt8Array();
}

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
    var company_name = apm_get_company_name_pedido_ventas();

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
                'Name': company_name,
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

function apm_to_string_pedido_ventas(value)
{
    if (value === null || value === undefined) {
        return '';
    }

    return String(value);
}

function apm_get_company_name_pedido_ventas()
{
    var candidates = [
        $('#empresa_descripcion').val(),
        $('#pdv_label').val(),
        $('.headempresap b').first().text(),
        $('.headempresa b').first().text()
    ];

    for (var i = 0; i < candidates.length; i++) {
        var value = apm_to_string_pedido_ventas(candidates[i]).trim();
        if (value !== '' && !/^\d+$/.test(value)) {
            return value;
        }
    }

    return '';
}
