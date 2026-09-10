// NODE_PATH=/path/to/node_modules node appsiel/tests/js/compras_cuenta_directa_test.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const dom = new JSDOM('<select id="forma_pago"><option value="credito">Crédito</option><option value="contado">Contado</option></select><div id="campo_cuenta_por_pagar_directa"><select id="cta_x_pagar_id"><option value="7">Cuenta directa</option></select></div>', {runScripts:'outside-only'});
const win = dom.window;
win.$ = win.jQuery = require('jquery')(win);
const $ = win.$;
const vista = fs.readFileSync(path.resolve(__dirname, '../../resources/views/compras/create.blade.php'), 'utf8');
const script = vista.match(/<script>\s*\$\(function \(\) \{[\s\S]*?mostrar_cuenta_por_pagar_directa\(\);[\s\S]*?<\/script>/)[0].replace(/<\/?script>/g, '');
win.eval(script);
win.eval(fs.readFileSync(path.resolve(__dirname, '../../../assets/js/compras/functions_create.js'), 'utf8'));
$(function () {
    assert.notEqual($('#campo_cuenta_por_pagar_directa').css('display'), 'none');
    $('#forma_pago').val('contado').trigger('change');
    assert.equal($('#campo_cuenta_por_pagar_directa').css('display'), 'none');
    $('#forma_pago').val('credito').trigger('change.cuentaDirecta');
    assert.notEqual($('#campo_cuenta_por_pagar_directa').css('display'), 'none');
    assert.equal($('#cta_x_pagar_id').val(), '7');
    win.reset_campos_formulario();
    assert.equal($('#campo_cuenta_por_pagar_directa').css('display'), 'none');
    console.log('OK: selector visible en crédito, oculto en contado y sincronizado al cambiar proveedor.');
    win.close();
});
