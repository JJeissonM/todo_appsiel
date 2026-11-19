const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

const flush = async () => { for (let i = 0; i < 20; i++) await Promise.resolve(); };

function setup(method = 'jspm', options = {}) {
    const result = { rows: [], alerts: [], sent: [], apm: [], texts: [], starts: 0, previews: 0 };
    const values = { metodo_impresion_pedido_ventas: method, impresora_cocina_por_defecto: options.noPrinter ? '' : 'COCINA' };
    let handler, resolveSend, rejectSend;
    const document = {};
    const button = { data: {
        lbl_consecutivo_doc_encabezado: '76961', lbl_fecha: '2026-11-19',
        lbl_cliente_descripcion: 'MESA 15', lbl_descripcion_doc_encabezado: 'Sin sal',
        lbl_total_factura: '$ 16.000', nombre_vendedor: 'DANIELA',
        core_tipo_transaccion_id: '42', core_tipo_doc_app_id: '9',
        lineas_registros: options.empty ? [] : [{
            lbl_producto_descripcion: 'ALMUERZO <ESPECIAL>', cantidad: '2', cantidad_numerica: 2,
            precio_unitario: '$ 8.000', precio_unitario_numerico: 8000,
            precio_total: '$ 16.000<span class="precio_total">16000</span>', precio_total_numerico: 16000
        }]
    } };
    class Element {
        constructor() { this.classes = []; this.children = []; this.value = ''; }
        addClass(value) { this.classes.push(value); return this; }
        text(value) { if (arguments.length) { this.value = String(value); return this; } return this.value; }
        append(child) { this.children.push(child); return this; }
        find(selector) { return this.children.find(child => child.classes.includes(selector.slice(1))); }
    }
    function $(selector) {
        if (selector === document) return { ready(callback) { callback(); } };
        if (selector instanceof Element) return selector;
        if (selector === button) return { data: key => button.data[key], attr: () => '/pedido/76961/imprimir' };
        if (selector === '<tr>' || selector === '<td>') return new Element();
        if (selector === '#tabla_lineas_registros tbody') return {
            empty() { result.rows = []; return this; }, append(row) { result.rows.push(row); return this; }
        };
        if (selector === '.btn_imprimir_en_cocina') return {
            on(event, callback) { handler = callback; },
            addClass() { result.disabled = true; return this; }, attr() { return this; },
            removeClass() { result.disabled = false; return this; }, removeAttr() { return this; }
        };
        return {
            val(value) { if (arguments.length) { values[selector.slice(1)] = value; return this; } return values[selector.slice(1)]; },
            on() {}, each(callback) { if (selector === '.linea_registro') result.rows.forEach(row => callback.call(row)); },
            first() { return this; }, text() { return ''; }
        };
    }
    $.each = (object, callback) => Object.keys(object).forEach(key => callback(key, object[key]));
    class Document {
        beep() { return this; } font() { return this; } align() { return this; }
        style() { return this; } size() { return this; } feed() { return this; } cut() { return this; }
        text(value) { result.texts.push(value); return this; }
        generateUInt8Array() { return new Uint8Array([27, 64]); }
    }
    const context = {
        $, document, Promise, Error, Date,
        Swal: { fire(alert) { result.alerts.push(alert); } },
        JSPM: {
            JSPrintManager: { websocket_status: options.closed ? 0 : 1, start() { result.starts++; } },
            WSStatus: { Open: 1 }, InstalledPrinter: function (name) { this.name = name; },
            ClientPrintJob: function () { this.sendToClient = () => {
                result.sent.push(this);
                return new Promise((resolve, reject) => { resolveSend = resolve; rejectSend = reject; });
            }; }
        },
        APM_CLIENT: { enqueuePrintJob(job) {
            result.apm.push(job);
            return new Promise((resolve, reject) => { resolveSend = resolve; rejectSend = reject; });
        } },
        Neodynamic: { JSESCPOSBuilder: { Document, FontFamily: { A: 0, B: 1 }, TextAlignment: { Center: 1 }, FontStyle: { Bold: 1 } } },
        open() { result.previews++; }, print() { throw Error('No debe imprimir la pagina del listado'); }
    };
    if (options.missingLibrary) delete context.JSPM;
    context.window = context;
    vm.createContext(context);
    for (const file of ['script_to_printer.js', 'pendientes_cocina.js']) {
        vm.runInContext(fs.readFileSync('assets/js/ventas/pedidos/' + file, 'utf8'), context);
    }
    return {
        result,
        click() { handler.call(button, { preventDefault() {} }); },
        resolve() { resolveSend({}); }, reject() { rejectSend(new Error('Impresora desconectada')); }
    };
}

(async () => {
    const jspm = setup();
    jspm.click();
    await flush();
    assert.strictEqual(jspm.result.starts, 1);
    assert.strictEqual(jspm.result.sent.length, 1);
    assert.strictEqual(jspm.result.sent[0].clientPrinter.name, 'COCINA');
    assert.ok(jspm.result.texts.includes('PEDIDO # 76961'));
    assert.ok(jspm.result.texts.includes('MESA 15'));
    assert.ok(jspm.result.texts.join('\n').includes('2 - ALMUERZO <ESPECIAL>'));
    assert.strictEqual(jspm.result.rows.length, 1, 'Conservar productos hasta completar el envio');
    assert.strictEqual(jspm.result.alerts.length, 0, 'No anunciar exito antes del envio');
    jspm.click();
    await flush();
    assert.strictEqual(jspm.result.sent.length, 1, 'Evitar envios simultaneos');
    jspm.reject();
    await flush();
    assert.strictEqual(jspm.result.alerts[0].icon, 'error');
    assert.strictEqual(jspm.result.rows.length, 0);
    assert.strictEqual(jspm.result.disabled, false);
    jspm.click();
    await flush();
    jspm.resolve();
    await flush();
    assert.strictEqual(jspm.result.sent.length, 2, 'Permitir reintentar');
    assert.strictEqual(jspm.result.alerts[1].icon, 'info');

    for (const options of [{ closed: true }, { noPrinter: true }, { missingLibrary: true }, { empty: true }]) {
        const failed = setup('jspm', options);
        failed.click(); await flush();
        assert.strictEqual(failed.result.sent.length, 0);
        assert.strictEqual(failed.result.alerts[0].icon, 'error');
        assert.strictEqual(failed.result.disabled, false);
    }
    const apm = setup('apm');
    apm.click(); await flush();
    assert.strictEqual(apm.result.starts, 0);
    assert.strictEqual(apm.result.sent.length, 0);
    assert.strictEqual(apm.result.apm[0].payload.Document.sale.Items[0].Total, 16000);
    assert.strictEqual(apm.result.apm[0].documentMeta.consecutivo, 76961);
    apm.resolve(); await flush();
    assert.strictEqual(apm.result.alerts[0].icon, 'info');
    const normal = setup('normal');
    normal.click();
    assert.strictEqual(normal.result.previews, 1);
    assert.strictEqual(normal.result.alerts.length, 0);
    console.log('OK: boton pendientes cocina, pedido seleccionado, JSPM, APM, errores, bloqueo y reintento.');
})().catch(error => { console.error(error); process.exit(1); });
