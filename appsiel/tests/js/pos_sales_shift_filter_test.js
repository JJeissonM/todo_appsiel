const fs = require('fs'), vm = require('vm'), assert = require('assert');
const document = {}, nodes = {};
function $(selector) {
  if (selector === document) return {ready: fn => fn()};
  return nodes[selector] || (nodes[selector] = {
    length: 1, value: '', attrs: {}, props: {}, events: {},
    val(v) {if (arguments.length) {this.value = v; return this;} return this.value;},
    attr(k,v) {if(arguments.length === 2) {this.attrs[k]=v; return this;} return this.attrs[k];},
    removeAttr(keys) {keys.split(' ').forEach(k => delete this.attrs[k]); return this;},
    data(k,v) {this.storage=this.storage||{}; if(arguments.length===2) {this.storage[k]=v; return this;} return this.storage[k];},
    remove() {return this;},
    text(v) {this.label = v; return this;},
    prop(k, v) {this.props[k] = v; return this;},
    on(events, selector, handler) {const fn=handler||selector; this.delegate=handler ? selector : null;events.split(' ').forEach(e => this.events[e] = fn); return this;},
    find(s) {return $(selector + ' ' + s);}, next(s) {return $(selector + ' ' + s);},
    add(other) {const self = this; return {prop(k,v) {self.prop(k,v); other.prop(k,v); return this;}};}
  });
}
$('#fecha_desde').val('2026-09-01'); $('#fecha_hasta').val('2026-09-15');
const search = $('#turno_operativo_busqueda').attr('id', 'turno_operativo_busqueda');
let forwarded = 0, aborted = 0;
const window = {ejecutar_acciones_con_item_sugerencia: () => forwarded++};
vm.runInNewContext(fs.readFileSync('assets/js/ventas_pos/reporte_filtro_turno.js','utf8'), {$, document, window, clearTimeout});
function selectShift(id = '1', opening = '2026-09-17 20:00:00') {
  search.val('T-' + id).attr('data-selected-label', 'T-' + id);
  $('#turno_operativo_id').val(id);
  const item = $('#item').attr('data-turno-opening-at', opening).attr('data-turno-closing-at', '2026-09-18 04:00:00');
  window.ejecutar_acciones_con_item_sugerencia(item, search);
}
function assertUnlocked() {
  assert.strictEqual($('#turno_operativo_id').val(), '');
  assert.strictEqual($('#fecha_desde').val(), '2026-09-01');
  assert.strictEqual($('#fecha_hasta').val(), '2026-09-15');
  for (const id of ['#fecha_desde','#fecha_hasta']) {
    assert.strictEqual($(id).props.required, true);
    assert.strictEqual($(id).props.disabled, false);
  }
}
selectShift();
for (const id of ['#fecha_desde','#fecha_hasta']) {
  assert.strictEqual($(id).props.required,false); assert.strictEqual($(id).props.disabled,true);
}
assert.strictEqual($('#fecha_desde').val(),'2026-09-17');
assert.strictEqual($('#fecha_hasta').val(),'2026-09-18');
assert($('#periodo_turno_resumen').label.includes('04:00:00'));
search.events.change();
assert.strictEqual($('#turno_operativo_id').val(), '1');
search.data('suggestions-request', {readyState:1, abort() {aborted++; this.readyState=4;}});
search.val('2012'); search.events.input();
assertUnlocked();
assert.strictEqual(search.val(), '2012');
assert.strictEqual(aborted, 1);
selectShift(); selectShift('2', '2026-09-16 08:00:00');
$('#limpiar_turno_resumen').events.click();
assertUnlocked(); assert.strictEqual(search.val(), '');
for (const event of ['change', 'comboboxselect']) {
  selectShift(); $('#form_consulta').events[event]();
  assertUnlocked(); assert.strictEqual(search.val(), '');
}
selectShift(); search.val(''); search.events.input(); assertUnlocked();
window.ejecutar_acciones_con_item_sugerencia($('#item'), $('#other').attr('id','other'));
assert.strictEqual(forwarded, 1);
console.log('OK: autocomplete, intervalo nocturno, edición de búsqueda, cambio de filtros, limpieza y restauración de fechas.');
