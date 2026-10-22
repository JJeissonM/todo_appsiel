const assert = require('assert');
const fs = require('fs');
const vm = require('vm');
const source = fs.readFileSync('appsiel/resources/views/contabilidad/auxiliar_por_cuenta.blade.php', 'utf8');
const script = source.match(/<script type="text\/javascript">([\s\S]*?)<\/script>/)[1];
const nodes = {};
let requests = 0, success, failure, complete;
const document = {};
function $(key) {
  if (key === document) return {ready: fn => fn()};
  if (typeof key === 'object') return key;
  if (!nodes[key]) nodes[key] = {
    attrs: {}, props: {}, events: {}, visible: true, content: '', value: '',
    ready(fn) { fn(); return this; }, focus() {return this;},
    keyup(fn) {return this;}, click(fn) {this.events.click = fn; return this;},
    on(event, fn) {this.events[event] = fn; return this;},
    val() {return this.value;}, next() {return this;}, fadeIn() {return this;},
    html(value) {if (arguments.length) {this.content = value; return this;} return this.content;},
    prop(key, value) {this.props[key] = value; return this;},
    attr(key, value) {if (arguments.length > 1) {this.attrs[key] = value; return this;} return this.attrs[key];},
    show() {this.visible = true; return this;}, hide() {this.visible = false; return this;},
    serialize() {return 'fecha_desde=2026-09-01&fecha_hasta=2026-09-15';}
  };
  return nodes[key];
}
$.post = (url, data, callback) => {
  requests++; success = callback;
  const pending = {fail(fn) {failure = fn; return pending;}, always(fn) {complete = fn; return pending;}};
  return pending;
};
$('#fecha_desde').value = '2026-09-01'; $('#fecha_hasta').value = '2026-09-15';
$('#btn_generar').content = 'Generar'; $('#btn_pdf').attrs.href = '/a3p0';
vm.runInNewContext(script, {$, document, alert() {throw new Error('Validación inesperada');}});
const submit = () => $('#form_consulta').events.submit({preventDefault() {}});
submit(); submit();
assert.strictEqual(requests, 1);
assert.strictEqual($('#btn_generar').props.disabled, true);
assert.strictEqual($('#auxiliar_cargando').visible, true);
assert.strictEqual($('#resultado_consulta').attrs['aria-busy'], 'true');
success('<table>Resultado</table>'); complete();
assert.strictEqual($('#resultado_consulta').content, '<table>Resultado</table>');
assert.strictEqual($('#auxiliar_cargando').visible, false);
assert.strictEqual($('#btn_generar').props.disabled, false);
submit(); failure(); complete();
assert.strictEqual(requests, 2);
assert($('#resultado_consulta').content.includes('No fue posible'));
assert.strictEqual($('#resultado_consulta').attrs['aria-busy'], 'false');
assert.strictEqual($('#auxiliar_cargando').visible, false);
assert.strictEqual($('#btn_generar').content, 'Generar');
assert.strictEqual($('#btn_generar').props.disabled, false);
console.log('OK: spinner, bloqueo de duplicados, éxito, error y recuperación.');
