const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const flush = async () => { for (let i = 0; i < 25; i++) await Promise.resolve(); };

function setup(options = {}) {
    const values = {
        '#metodo_impresion_pedido_ventas': options.method || 'normal',
        '#usar_complemento_JSPrintManager': options.disabled ? '0' : '1',
        '#enviar_impresion_directamente_a_la_impresora': options.mode || '1',
        '#impresora_cocina_por_defecto': 'APM-PDV-DIFERENTE',
        '#jspm_impresora_cocina_por_defecto': options.noPrinter ? '' : 'POS-80C',
        '#action': 'create', '#formato_impresion_pedidos': options.quote ? 'cotizacion' : 'estandar'
    };
    const labels = {
        '.lbl_consecutivo_doc_encabezado': '', '#lbl_fecha': '2026-11-21',
        '.lbl_cliente_descripcion': 'MESA 29', '.lbl_descripcion_doc_encabezado': 'Sin sal',
        '.lbl_total_factura': '$ 16.000', '.lbl_atendido_por': 'DANIELA'
    };
    let cleared = false, finish;
    const result = { starts: 0, jobs: [], apm: [], alerts: [], texts: [], previews: 0, quotes: 0, resets: 0 };
    const row = { find(selector) { return {
        text: () => ({ '.lbl_producto_descripcion': 'ALMUERZO ESPECIAL', '.cantidad': '2', '.precio_total': '16000' })[selector] || '',
        eq: () => ({ text: () => '8000' })
    }; } };
    const document = {};
    const $ = selector => selector === document ? { ready(callback) { callback(); } } : selector === row ? row : {
        val: () => values[selector],
        text(value) { if (arguments.length) labels[selector] = value; return cleared ? '' : labels[selector] || ''; },
        first() { return this; }, attr() { return this; }, html() { return this; },
        each(callback) { if (!cleared && selector === '.linea_registro') callback.call(row); }
    };
    class Document {
        beep() { return this; } font() { return this; } align() { return this; } style() { return this; }
        size() { return this; } feed() { return this; } cut() { return this; }
        text(value) { result.texts.push(value); return this; }
        generateUInt8Array() { return new Uint8Array([27, 64]); }
    }
    const context = {
        $, document, Promise, Error, Date, url_raiz: '/app',
        confirm: () => !options.decline,
        APPSIEL_JSPM: {
            start() { result.starts++; },
            send(printer, commands) {
                if (!printer) return Promise.reject(new Error('Configure la impresora de cocina.'));
                result.jobs.push({ printer, commands });
                return options.error ? Promise.reject(new Error('No se pudo conectar')) : new Promise(resolve => { finish = resolve; });
            }
        },
        APM_CLIENT: { enqueuePrintJob(job) { result.apm.push(job); return Promise.resolve({}); } },
        Neodynamic: { JSESCPOSBuilder: { Document, FontFamily: { A: 0, B: 1 }, TextAlignment: { Center: 1 }, FontStyle: { Bold: 1 } } },
        Swal: { fire(alert) { result.alerts.push(alert); } },
        ventana_imprimir() { result.previews++; return true; },
        ventana_imprimir_cotizacion() { result.quotes++; return true; },
        pedidos_notificar_popup_bloqueado() { throw new Error('Popup bloqueado'); },
        llenar_tabla_productos_facturados() {},
        resetear_ventana() { cleared = true; result.resets++; }
    };
    context.window = context;
    vm.createContext(context);
    vm.runInContext(fs.readFileSync('assets/js/ventas_pos/pedidos/script_to_printer.js', 'utf8'), context);
    const source = fs.readFileSync('assets/js/ventas_pos/pedidos/commons.js', 'utf8');
    const from = source.indexOf('function (doc_encabezado_consecutivo) {');
    const to = source.indexOf('\n        });', from);
    assert.ok(from >= 0 && to > from);
    vm.runInContext('saveCallback = ' + source.slice(from, to) + '};', context);
    return { result, save() { context.saveCallback(77150); }, finish() { finish(); } };
}

(async () => {
    for (const options of [{}, { mode: '2' }]) {
        const scenario = setup(options);
        scenario.save();
        assert.strictEqual(scenario.result.starts, 1);
        assert.strictEqual(scenario.result.previews, 0, 'JSPrintManager no debe abrir la impresion del navegador');
        assert.strictEqual(scenario.result.jobs[0].printer, 'POS-80C');
        assert.ok(scenario.result.texts.includes('PEDIDO # 77150'));
        assert.ok(scenario.result.texts.includes('MESA 29'));
        assert.ok(scenario.result.texts.join('\n').includes('2 - ALMUERZO ESPECIAL'));
        assert.strictEqual(scenario.result.resets, 1, 'Capturar comandos antes de limpiar el pedido guardado');
        assert.strictEqual(scenario.result.alerts.length, 1, 'Esperar confirmacion antes de anunciar exito');
        scenario.finish(); await flush();
        assert.strictEqual(scenario.result.alerts[1].icon, 'info');
    }
    for (const options of [{ noPrinter: true }, { error: true }]) {
        const failed = setup(options);
        failed.save(); await flush();
        assert.strictEqual(failed.result.alerts[1].icon, 'error');
        assert.strictEqual(failed.result.previews, 0, 'No ocultar un fallo abriendo impresion normal');
        assert.strictEqual(failed.result.resets, 1);
    }
    for (const options of [{ disabled: true }, { mode: '0' }, { mode: '2', decline: true }]) {
        const normal = setup(options);
        normal.save(); await flush();
        assert.strictEqual(normal.result.jobs.length, 0);
        assert.strictEqual(normal.result.previews, 1);
    }
    const apm = setup({ method: 'apm' });
    apm.save(); await flush();
    assert.strictEqual(apm.result.starts, 0);
    assert.strictEqual(apm.result.previews, 0);
    assert.strictEqual(apm.result.apm[0].documentMeta.consecutivo, 77150);
    assert.strictEqual(apm.result.apm[0].payload.Document.sale.Items[0].Qty, 2);
    const quote = setup({ quote: true });
    quote.save();
    assert.strictEqual(quote.result.quotes, 1);
    assert.strictEqual(quote.result.jobs.length, 0);
    console.log('OK: guardar pedido POS con JSPM, sin ventana normal, captura antes de limpiar, errores, confirmacion, APM y cotizacion.');
})().catch(error => { console.error(error); process.exit(1); });
