const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

function setup(options = {}) {
    const values = {
        '#metodo_impresion_pedido_restaurante': options.method || 'normal',
        '#metodo_impresion_factura_pos': 'normal',
        '#usar_complemento_JSPrintManager': options.enabled === false ? '0' : '1',
        '#enviar_impresion_directamente_a_la_impresora': options.mode || '1',
        '#imprimir_factura_automaticamente': '0',
        '#impresora_cocina_por_defecto': 'APM-PDV-DIFERENTE',
        '#jspm_impresora_cocina_por_defecto': options.noPrinter ? '' : 'COCINA POS',
        '#action': 'create',
        '#valor_total_factura': '45000',
        '#tamanio_letra_impresion_items_cocina': '1'
    };
    const result = { starts: 0, sent: [], alerts: [], ajax: [], apm: 0, previews: 0, resets: 0, texts: [] };
    let cleared = false;
    const row = { find(selector) { return { text: () => ({
        '.lbl_producto_descripcion': 'Plato con descripcion extensa de mas de cuarenta caracteres',
        '.cantidad': '2', '.lbl_observaciones': 'Sin cebolla', '.precio_total': '45000', '.precio_unitario': '22500'
    })[selector] || '' }; } };
    const document = {};
    function $(selector) {
        if (selector === document) return { ready(callback) { callback(); } };
        if (selector === row) return row;
        return {
            val: () => cleared && selector === '#valor_total_factura' ? '0' : values[selector],
            text() { return ''; },
            each(callback) { if (!cleared) callback.call(row); },
            show() { return this; }, hide() { return this; }, css() { return this; }
        };
    }
    $.ajax = data => result.ajax.push(data);
    class Document {
        beep() { return this; } font() { return this; } align() { return this; }
        style() { return this; } size() { return this; } feed() { return this; } cut() { return this; }
        text(value) { result.texts.push(value); return this; }
        generateUInt8Array() { return new Uint8Array([27, 64]); }
    }
    const context = {
        $, document, Promise, Error, setTimeout, clearTimeout, url_raiz: '/app',
        confirm: () => options.confirm !== false,
        Swal: { fire(data) { result.alerts.push(data); } },
        ventana_imprimir() { result.previews++; return true; },
        resetear_ventana() { cleared = true; result.resets++; },
        JSPM: {
            JSPrintManager: { websocket_status: options.closed ? 0 : 1, start() { result.starts++; } },
            WSStatus: { Open: 1 },
            InstalledPrinter: function (name) { this.name = name; },
            ClientPrintJob: function () {
                this.sendToClient = () => {
                    result.sent.push(this);
                    return options.sendError ? Promise.reject(new Error('Envio fallido')) : Promise.resolve();
                };
            }
        },
        Neodynamic: { JSESCPOSBuilder: { Document, FontFamily: { A: 0, B: 1 }, TextAlignment: { Center: 1 }, FontStyle: { Bold: 1 } } }
    };
    if (options.missingLibrary) delete context.JSPM;
    context.window = context;
    vm.createContext(context);
    vm.runInContext(fs.readFileSync('assets/js/ventas_pos/external_print/jspm_client.js', 'utf8'), context);
    context.APPSIEL_JSPM.connectionTimeoutMs = 0;
    vm.runInContext(fs.readFileSync('assets/js/ventas_pos/script_to_printer.js', 'utf8'), context);
    context.print_comanda_apm = () => { result.apm++; return Promise.resolve({}); };
    context.pos_mostrar_modal_apm = () => {};
    return {
        result,
        async print(prefactura = false) {
            context.enviar_impresion(prefactura ? 'prefactura' : {
                doc_encabezado_documento_transaccion_prefijo_consecutivo: 'POS-321',
                doc_encabezado_fecha: '2026-11-19',
                doc_encabezado_tercero_nombre_completo: 'Cliente POS',
                doc_encabezado_vendedor_descripcion: 'Cajero',
                doc_encabezado_descripcion: 'Para llevar'
            });
            for (let i = 0; i < 30; i++) await Promise.resolve();
        }
    };
}

(async () => {
    for (const mode of ['1', '2']) {
        const scenario = setup({ mode });
        await scenario.print();
        assert.strictEqual(scenario.result.starts, 1);
        assert.strictEqual(scenario.result.sent.length, 1);
        assert.strictEqual(scenario.result.sent[0].clientPrinter.name, 'COCINA POS');
        assert.ok(scenario.result.sent[0].binaryPrinterCommands instanceof Uint8Array);
        assert.ok(scenario.result.texts.includes('COMANDA POS-321'));
        assert.ok(scenario.result.texts.includes('Cliente POS'));
        const lines = scenario.result.texts[scenario.result.texts.length - 1];
        assert.ok(lines.includes('2 - Plato con descripcio'));
        assert.ok(lines.includes('Obs: Sin cebolla'));
        assert.ok(lines.includes('Detalle: Para llevar'));
        assert.ok(lines.includes('VENTA TOTAL: 45000'));
        assert.strictEqual(scenario.result.resets, 1);
        assert.strictEqual(scenario.result.previews, 1);
        assert.strictEqual(scenario.result.ajax.length, 0);
        assert.strictEqual(scenario.result.alerts[0].icon, 'info');
    }
    for (const options of [{ mode: '0' }, { mode: '2', confirm: false }]) {
        const scenario = setup(options);
        await scenario.print();
        assert.strictEqual(scenario.result.sent.length, 0);
    }
    for (const options of [{ closed: true }, { noPrinter: true }, { missingLibrary: true }, { sendError: true }]) {
        const scenario = setup(options);
        await scenario.print();
        assert.strictEqual(scenario.result.alerts[0].icon, 'error');
        assert.strictEqual(scenario.result.resets, 1);
        assert.strictEqual(scenario.result.ajax.length, 0);
    }
    const apm = setup({ method: 'apm' });
    await apm.print();
    assert.strictEqual(apm.result.starts, 0);
    assert.strictEqual(apm.result.apm, 1);
    assert.strictEqual(apm.result.sent.length, 0);
    const server = setup({ enabled: false });
    await server.print();
    assert.strictEqual(server.result.starts, 0);
    assert.strictEqual(server.result.ajax.length, 1);
    assert.strictEqual(server.result.sent.length, 0);
    const preview = setup();
    await preview.print(true);
    assert.strictEqual(preview.result.sent.length, 0);
    assert.strictEqual(preview.result.resets, 0);
    console.log('OK: cocina POS con JSPM, confirmacion, captura antes de limpiar, APM, servidor y errores.');
})().catch(error => { console.error(error); process.exit(1); });
