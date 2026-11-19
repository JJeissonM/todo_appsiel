const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

function setup(method, options = {}) {
    const values = {
        '#metodo_impresion_pedido_ventas': method,
        '#impresora_cocina_por_defecto': options.noPrinter ? '' : 'COCINA',
        '#lbl_consecutivo_doc_encabezado': '123',
        '#lbl_fecha': '2026-11-19',
        '#lbl_cliente_descripcion': 'Cliente',
        '#nombre_vendedor': 'Vendedor',
        '#lbl_descripcion_doc_encabezado': 'Sin sal',
        '#lbl_total_factura': '$ 25.000',
        '#tamanio_letra_impresion_items_cocina': '1'
    };
    const result = { starts: 0, sent: [], alerts: [], printed: 0, apm: 0, text: [] };
    const itemName = 'Producto con descripcion muy extensa para imprimir completamente';
    const row = { find(selector) { return { text: () => ({
        '.lbl_producto_descripcion': itemName, '.cantidad': '2'
    })[selector] }; } };
    let click;
    const document = {};
    function $(selector) {
        if (selector === document) return { ready(callback) { callback(); } };
        if (selector === row) return row;
        return {
            val: () => values[selector],
            on(event, callback) { click = callback; },
            each(callback) { callback.call(row); }
        };
    }
    class Document {
        beep() { return this; }
        font() { return this; }
        align() { return this; }
        style() { return this; }
        size() { return this; }
        text(value) { result.text.push(value); return this; }
        feed() { return this; }
        cut() { return this; }
        generateUInt8Array() { return new Uint8Array([27, 64]); }
    }
    const context = {
        $, document, Promise, Error,
        Swal: { fire(value) { result.alerts.push(value); } },
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
        Neodynamic: { JSESCPOSBuilder: { Document, FontFamily: { A: 0, B: 1 }, TextAlignment: { Center: 1 }, FontStyle: { Bold: 1 } } },
        print() { result.printed++; }
    };
    context.window = context;
    vm.createContext(context);
    vm.runInContext(fs.readFileSync('assets/js/ventas/pedidos/script_to_printer.js', 'utf8'), context);
    context.print_comanda_apm = () => { result.apm++; return Promise.resolve({ CopyLabel: 'ORIGINAL' }); };
    return {
        result, itemName,
        async click() {
            click({ preventDefault() {} });
            for (let i = 0; i < 10; i++) await Promise.resolve();
        }
    };
}

(async () => {
    const legacy = setup('jspm');
    assert.strictEqual(legacy.result.starts, 1);
    await legacy.click();
    assert.strictEqual(legacy.result.sent.length, 1);
    assert.strictEqual(legacy.result.sent[0].clientPrinter.name, 'COCINA');
    assert.ok(legacy.result.sent[0].binaryPrinterCommands instanceof Uint8Array);
    assert.ok(legacy.result.text.includes('PEDIDO # 123'));
    const lines = legacy.result.text[legacy.result.text.length - 1];
    for (let i = 0; i < legacy.itemName.length; i += 20) {
        assert.ok(lines.includes(legacy.itemName.substring(i, i + 20)));
    }
    assert.ok(lines.includes('Detalle: Sin sal'));
    assert.ok(lines.includes('VENTA TOTAL: $ 25.000'));
    assert.strictEqual(legacy.result.alerts[0].icon, 'info');

    for (const options of [{ closed: true }, { noPrinter: true }, { sendError: true }]) {
        const failed = setup('jspm', options);
        await failed.click();
        assert.strictEqual(failed.result.alerts.length, 1);
        assert.strictEqual(failed.result.alerts[0].icon, 'error');
        if (!options.sendError) assert.strictEqual(failed.result.sent.length, 0);
    }

    for (const method of ['normal', 'apm']) {
        const other = setup(method);
        assert.strictEqual(other.result.starts, 0);
        await other.click();
        assert.strictEqual(other.result.sent.length, 0);
        assert.strictEqual(other.result.printed, method === 'normal' ? 1 : 0);
        assert.strictEqual(other.result.apm, method === 'apm' ? 1 : 0);
    }
    console.log('OK: pedidos JSPrintManager, APM, normal y errores de envio.');
})().catch(error => { console.error(error); process.exit(1); });
