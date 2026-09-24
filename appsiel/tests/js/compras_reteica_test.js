// NODE_PATH=/path/to/node_modules node appsiel/tests/js/compras_reteica_test.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const root = path.resolve(__dirname, '../../..');
const partial = fs.readFileSync(path.join(root, 'appsiel/resources/views/compras/incluir/reteica.blade.php'), 'utf8');
for (const name of ['reteica_base','reteica_base_manual']) {
    assert.match(partial, new RegExp('name="' + name + '" form="form_create"'));
}
const dom = new JSDOM(`<!doctype html><form id="form_create"><input id="reteica_retencion_id" value="0"></form>
<button id="btn_guardar"></button><input id="maneja_retenciones_compras" value="1">
<input id="valor_total_retefuente"><input id="retencion_id"><div id="subtotal"></div><div id="total_factura"></div>
<div id="total_impuestos"></div><div id="descuento"></div><div id="lbl_total_retefuente"></div>
<div id="reteica_etiqueta"></div><div id="reteica_importe"></div><div id="reteica_base_preview"></div>
<div id="reteica_editor" style="display:none"><select id="reteica_select"><option value="0"></option>
<option value="1" data-tasa="0.414">4.14 por mil</option><option value="2" data-tasa="0.7">7 por mil</option></select>
<input id="reteica_preview"><input id="reteica_base" name="reteica_base" form="form_create"><input id="reteica_base_manual" name="reteica_base_manual" form="form_create" value="0"></div><button id="reteica_add"></button><button id="reteica_confirmar"></button>
<button id="reteica_editar"></button><button id="reteica_reset"></button>
<table><tr class="linea_registro"><td class="base_impuesto">4500000</td><td class="precio_total">5355000</td>
<td class="valor_impuesto">855000</td><td class="valor_total_descuento">500000</td><td class="valor_retencion">112500</td></tr></table>`, {runScripts:'outside-only'});
const win = dom.window;
const $ = require('jquery')(win);
win.$ = win.jQuery = $;
// jsdom no calcula layout; visibilidad de los controles se determina por su estilo.
$.expr.pseudos.visible = element => element.style.display !== 'none';
let alerts = 0;
win.alert = () => alerts++;
win.eval(fs.readFileSync(path.join(root, 'assets/js/compras/functions_create.js'), 'utf8'));
win.eval(fs.readFileSync(path.join(root, 'assets/js/compras/reteica.js'), 'utf8'));
$(async function () {
    win.calcular_totales();
    assert.equal($('#total_factura').text(), '$ 5.242.500');
    $('#reteica_add').trigger('click');
    $('#reteica_select').val('1').trigger('change');
    assert.equal($('#reteica_preview').val(), '18630.00');
    assert.equal($('#total_factura').text(), '$ 5.223.870');
    win.document.getElementById('btn_guardar').click();
    assert.equal(alerts, 1, 'Bloquea el guardado mientras se edita');
    $('#reteica_confirmar').trigger('click');
    assert.equal($('#reteica_retencion_id').val(), '1');
    win.calcular_totales();
    assert.equal($('#total_factura').text(), '$ 5.223.870', 'Recalcular no duplica la retención');
    $('.base_impuesto').text('9000000');
    win.calcular_totales();
    assert.equal($('#reteica_preview').val(), '37260.00', 'Actualiza al cambiar las líneas');
    $('#reteica_editar').trigger('click');
    $('#reteica_select').val('2').trigger('change');
    $('#reteica_confirmar').trigger('click');
    assert.equal($('#reteica_preview').val(), '63000.00');
    assert.equal($('#reteica_retencion_id').val(), '2');
    $('#reteica_editar').trigger('click');
    $('#reteica_base').val('1000000').trigger('input');
    assert.equal($('#reteica_preview').val(), '7000.00');
    const enviados = new URLSearchParams($('#form_create').serialize());
    assert.equal(enviados.get('reteica_base'), '1000000');
    assert.equal(enviados.get('reteica_base_manual'), '1');
    $('.base_impuesto').text('12000000');
    win.calcular_totales();
    assert.equal($('#reteica_base').val(), '1000000');
    assert.equal($('#reteica_preview').val(), '7000.00');
    $('#reteica_base').val('-1').trigger('input');
    $('#reteica_confirmar').trigger('click');
    assert.equal($('#reteica_retencion_id').val(), '0');
    $('#reteica_base').val('0').trigger('input');
    $('#reteica_confirmar').trigger('click');
    assert.equal($('#reteica_retencion_id').val(), '2');
    assert.equal($('#reteica_preview').val(), '0.00');
    $('#reteica_reset').trigger('click');
    assert.equal($('#reteica_base_manual').val(), '0');
    assert.equal($('#reteica_retencion_id').val(), '0');
    assert.equal($('#total_factura').text(), '$ 5.242.500');
    $('.linea_registro').remove();
    win.calcular_totales();
    assert.equal($('#reteica_preview').val(), '0.00');
    assert.equal($('#total_factura').text(), '$ 0');
    console.log('OK: cálculo neto, cambios de base, confirmación, edición, reset y bloqueo de guardado.');
    win.close();
});
