$(document).ready(function () {
    if (pedidos_metodo_impresion_cocina() === 'jspm' && window.APPSIEL_JSPM) {
        window.APPSIEL_JSPM.start();
    }
});

function pedidos_metodo_impresion_cocina() {
    if ($('#metodo_impresion_pedido_ventas').val() === 'apm') {
        return 'apm';
    }
    return $('#usar_complemento_JSPrintManager').val() === '1' ? 'jspm' : 'normal';
}

function pedidos_imprimir_guardado() {
    var metodo = pedidos_metodo_impresion_cocina();
    var modo = $('#enviar_impresion_directamente_a_la_impresora').val();
    var directo = modo === '1';
    if (metodo !== 'normal' && modo === '2') {
        directo = confirm('¿Quiere imprimir el PEDIDO en la cocina?');
    }
    if (metodo === 'normal' || !directo) {
        if (!ventana_imprimir()) {
            pedidos_notificar_popup_bloqueado();
        }
        return Promise.resolve();
    }

    Swal.fire({
        title: 'Enviando pedido a cocina',
        text: 'Esperando conexión y confirmación de la impresora…',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        onOpen: function () { Swal.showLoading(); }
    });
    // print_comanda captura el pedido antes de que commons limpie el formulario.
    return print_comanda().then(function () {
        Swal.fire({ icon: 'info', title: 'Impresión enviada', text: 'Pedido enviado a la impresora de COCINA.' });
    }).catch(function (error) {
        Swal.fire({
            icon: 'error',
            title: 'Pedido guardado con novedad de impresión',
            text: error && (error.ErrorMessage || error.message) || 'No fue posible enviar el pedido a la cocina. Revise la impresora antes de reintentar.'
        });
    });
}

function crear_comanda_jspm_pedido_pos() {
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
    lineas += 'Detalle: ' + $('.lbl_descripcion_doc_encabezado').first().text() + '\n';
    lineas += '# TOTAL ITEMS: ' + cantidad_items + '\n';
    lineas += 'VENTA TOTAL: ' + $('.lbl_total_factura').first().text() + '\n';
    return doc.beep(3, 2)
        .font(escpos.FontFamily.A).align(escpos.TextAlignment.Center).style([escpos.FontStyle.Bold])
        .size(0, 1)
        .text('PEDIDO # ' + $('.lbl_consecutivo_doc_encabezado').first().text())
        .text('F. ' + $('#lbl_fecha').text())
        .text($('.lbl_cliente_descripcion').first().text())
        .text('Atiende: ' + $('.lbl_atendido_por').first().text())
        .font(escpos.FontFamily.B)
        .size(1, parseFloat($('#tamanio_letra_impresion_items_cocina').val()) || 0)
        .text(lineas).feed(5).cut().generateUInt8Array();
}

function print_comanda() {
    if (pedidos_metodo_impresion_cocina() === 'jspm') {
        try {
            if (!window.APPSIEL_JSPM) {
                throw new Error('No se pudo cargar la conexión con JSPrintManager. Recargue la página.');
            }
            return window.APPSIEL_JSPM.send($('#jspm_impresora_cocina_por_defecto').val(), crear_comanda_jspm_pedido_pos());
        } catch (error) {
            return Promise.reject(error);
        }
    }
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
    var consecutivo = parseInt(($('.lbl_consecutivo_doc_encabezado').text() || '0'), 10) || 0;

    return {
        core_empresa_id: parseInt($('#core_empresa_id').val() || '1', 10) || 1,
        core_tipo_transaccion_id: parseInt($('#core_tipo_transaccion_id').val() || '42', 10) || 42,
        core_tipo_doc_app_id: parseInt($('#core_tipo_doc_app_id').val() || '0', 10) || 0,
        consecutivo: consecutivo,
        document_type: 'ticket_venta',
        document_label: $('.lbl_consecutivo_doc_encabezado').text() || ('Pedido ' + consecutivo)
    };
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
                'Customer': $('.lbl_cliente_descripcion').text() || '',
                'COPY': 'COPIA # 1'
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
                'Total': total_factura,
                'COPY': 'COPIA # 1'
            },
            'footer': ['Pedido de ventas']
        }
    };
}
