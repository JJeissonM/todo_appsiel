const assert = require('assert');
const fs = require('fs');
const vm = require('vm');

async function run(method, fail = false) {
    const values = {
        '#metodo_impresion_factura_pos': method, '#numero_identificacion': '123456789',
        '#action': 'create', '#uniqid': 'venta-original', '#usar_complemento_JSPrintManager': '1'
    };
    let click;
    let cleared = false;
    const result = { previews: 0, resets: 0, apm: 0, errors: 0, requests: [] };
    const document = {};
    function $(selector) {
        if (selector === document) return { ready(callback) { callback(); } };
        return {
            val(value) { if (arguments.length) values[selector] = value; return values[selector]; },
            click(callback) { click = callback; },
            attr(name) { return name === 'action' ? '/pos_factura' : this; },
            html() { return this; }, removeAttr() { return this; }
        };
    }
    $.post = (url, data, success) => {
        result.requests.push({ url, data });
        if (!fail) success({
            url_print: '/factura_electronica/123/imprimir',
            doc_encabezado: { doc_encabezado_documento_transaccion_prefijo_consecutivo: 'FE-123' },
            factura_electronica: { cufe: 'cufe-prueba' }
        });
        const request = {
            fail(callback) { if (fail) callback({ status: 422 }); return request; },
            always(callback) { callback(); return request; }
        };
        return request;
    };
    const context = {
        $, document, Promise, locked: false, hay_productos: 1,
        validar_producto_con_contorno: () => true,
        pos_preparar_payload_guardado: () => ({ data: values['#uniqid'] }),
        pos_reset_contexto_despues_guardado() { values['#uniqid'] = 'venta-nueva'; },
        pos_mostrar_mensaje_error_guardado() { result.errors++; },
        resetear_ventana() { cleared = true; result.resets++; },
        enfocar_tab_totales() {},
        Swal: { fire() { return Promise.resolve({}); } }
    };
    context.window = context;
    // Sin APM_CLIENT: este es el entorno real cuando APM está desactivado.
    vm.createContext(context);
    vm.runInContext(fs.readFileSync('assets/js/ventas_pos/factura_electronica.js', 'utf8'), context);
    context.ventana_imprimir_fe = url => { result.previews++; assert.ok(url.includes('/123/')); return {}; };
    context.crear_payload_apm_factura_electronica = (header, electronic) => {
        assert.strictEqual(cleared, false, 'Capturar factura antes de limpiar registros');
        assert.strictEqual(electronic.cufe, 'cufe-prueba');
        return { PrinterId: 'APM POS' };
    };
    context.crear_meta_apm_factura_pos = () => ({});
    context.pos_previsualizar_y_preguntar_factura_electronica_apm_preparada = data => {
        assert.strictEqual(data.meta.document_type, 'factura_electronica');
        result.apm++;
        return Promise.resolve(true);
    };
    click.call({}, { preventDefault() {} });
    for (let i = 0; i < 10; i++) await Promise.resolve();
    assert.strictEqual(context.locked, false);
    assert.strictEqual(result.requests[0].url, '/pos_factura_electronica');
    assert.strictEqual(result.requests[0].data, 'venta-original');
    if (fail) {
        assert.strictEqual(result.errors, 1);
        assert.strictEqual(result.resets, 0);
        assert.strictEqual(values['#uniqid'], 'venta-original');
    } else {
        assert.strictEqual(result.resets, 1);
        assert.strictEqual(values['#uniqid'], 'venta-nueva');
        assert.strictEqual(result.apm, method === 'apm' ? 1 : 0);
        assert.strictEqual(result.previews, method === 'apm' ? 0 : 1);
    }
}

(async () => {
    await run('normal');
    await run('apm');
    await run('normal', true);
    await run('apm', true);
    console.log('OK: facturacion electronica con y sin APM, previsualizacion y conservacion de venta ante error.');
})().catch(error => { console.error(error); process.exit(1); });
