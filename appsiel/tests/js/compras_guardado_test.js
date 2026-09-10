// NODE_PATH=/path/to/node_modules node appsiel/tests/js/compras_guardado_test.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {JSDOM} = require('jsdom');
const dom = new JSDOM(`<!doctype html><form id="form_create" action="/compras">
<input name="proveedor_id" value="15"><input id="fecha" name="fecha" value="2026-09-10" disabled>
<textarea name="descripcion">Compra pendiente de validar</textarea>
<input name="reteica_retencion_id" value="3"><input name="lineas_registros" value='[{"cantidad":2}]'>
<input name="lineas_registros_medios_recaudo" value='[{"valor":"$100"}]'></form>
<button id="btn_guardar">Guardar</button><div id="div_cargando" style="display:none"></div>
<table id="ingreso_registros"><thead><tr><th>cantidad</th></tr></thead><tbody>
<tr class="linea_registro"><td>2</td></tr><tr id="linea_ingreso_default"><td><input value="5"></td></tr></tbody></table>`, {runScripts:'outside-only'});
const win = dom.window;
const $ = require('jquery')(win);
win.$ = win.jQuery = $;
const messages = [];
win.Swal = {fire: message => messages.push(message)};
win.eval(fs.readFileSync(path.resolve(__dirname, '../../../assets/js/compras/functions_create.js'), 'utf8'));
win.eval(fs.readFileSync(path.resolve(__dirname, '../../../assets/js/jquery.tabletojson.min.js'), 'utf8'));
let pending, requests = 0, sent;
$.ajax = options => {
    requests++;
    sent = options;
    pending = $.Deferred();
    return pending.promise();
};
const before = $('#form_create').html();
win.enviar_formulario_compra();
assert.equal($('#btn_guardar').prop('disabled'), true);
assert.ok(sent.data.includes('fecha=2026-09-10'), 'Se envía la fecha aunque esté deshabilitada');
assert.equal($('#fecha').prop('disabled'), true);
win.enviar_formulario_compra();
assert.equal(requests, 1, 'No duplica el envío mientras está pendiente');
pending.reject({status:422, responseJSON:{message:'Debe seleccionar una bodega activa de la empresa.'}});
assert.equal(messages[0].text, 'Debe seleccionar una bodega activa de la empresa.');
assert.equal($('#form_create').html(), before, 'Mantiene cabecera, retenciones, líneas y pagos');
assert.equal($('#linea_ingreso_default input').val(), '5', 'Mantiene la línea en edición');
assert.equal($('.linea_registro').length, 1);
assert.equal($('#btn_guardar').prop('disabled'), false, 'Permite reintentar');
assert.equal($('#div_cargando').css('display'), 'none');
win.enviar_formulario_compra();
pending.reject({status:422, responseJSON:{inv_bodega_id:['Bodega no válida.']}});
assert.equal(messages[1].text, 'Bodega no válida.', 'Acepta el formato de validación de Laravel 5.2');
win.enviar_formulario_compra();
pending.reject({status:0});
assert.equal($('#form_create').html(), before, 'Un error de red también conserva los datos');
// El plugin permite excluir temporalmente la fila de captura sin eliminarla.
$('#linea_ingreso_default').data('ignore', true);
assert.deepEqual(JSON.parse(JSON.stringify($('#ingreso_registros').tableToJSON({ignoreHiddenRows:false}))), [{cantidad:'2'}]);
$('#linea_ingreso_default').data('ignore', false);
assert.equal($('#linea_ingreso_default input').val(), '5');
console.log('OK: errores de bodega, validación y red conservan los datos; reintento y exclusión de fila de captura.');
win.close();
