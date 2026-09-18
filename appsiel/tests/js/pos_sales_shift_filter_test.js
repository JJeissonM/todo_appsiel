const fs = require('fs'), vm = require('vm'), assert = require('assert');
const document = {}, nodes = {};
function $(selector) {
  if (selector === document) return {ready: fn => fn()};
  return nodes[selector] || (nodes[selector] = {
    length: 1, value: '', attrs: {}, props: {}, events: {},
    val(v) {if (arguments.length) {this.value = v; return this;} return this.value;},
    attr(k) {return this.attrs[k];},
    text(v) {this.label = v; return this;},
    prop(k, v) {this.props[k] = v; return this;},
    on(events, fn) {events.split(' ').forEach(e => this.events[e] = fn); return this;},
    find(s) {return $(selector + ' ' + s);}, next(s) {return $(selector + ' ' + s);},
    add(other) {const self = this; return {prop(k,v) {self.prop(k,v); other.prop(k,v); return this;}};}
  });
}
$('#fecha_desde').val('2026-09-01'); $('#fecha_hasta').val('2026-09-15');
const option = $('#turno_operativo_id option:selected');
option.attrs = {'data-desde':'2026-09-17','data-hasta':'2026-09-18','data-intervalo':'2026-09-17 20:00:00 — 2026-09-18 04:00:00'};
vm.runInNewContext(fs.readFileSync('assets/js/ventas_pos/reporte_filtro_turno.js','utf8'), {$, document});
assert.strictEqual($('#fecha_desde').props.required, true);
$('#turno_operativo_id').val('1'); $('#turno_operativo_id').events.comboboxselect();
for (const id of ['#fecha_desde','#fecha_hasta']) {
  assert.strictEqual($(id).props.required,false); assert.strictEqual($(id).props.disabled,true);
}
assert.strictEqual($('#fecha_desde').val(),'2026-09-17');
assert.strictEqual($('#fecha_hasta').val(),'2026-09-18');
assert($('#periodo_turno_resumen').label.includes('04:00:00'));
$('#turno_operativo_id').val(''); $('#turno_operativo_id').events.change();
assert.strictEqual($('#fecha_desde').val(),'2026-09-01');
assert.strictEqual($('#fecha_hasta').val(),'2026-09-15');
assert.strictEqual($('#fecha_desde').props.required,true);
assert.strictEqual($('#fecha_desde').props.disabled,false);
console.log('OK: selector de turno, intervalo nocturno, fechas opcionales y restauración de fechas manuales.');
