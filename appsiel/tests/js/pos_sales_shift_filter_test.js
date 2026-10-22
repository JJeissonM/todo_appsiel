const fs = require('fs'), vm = require('vm'), assert = require('assert');
const document = {}, nodes = {};
let pendingTimer; const requests = [];
$.trim = value => value.trim();
$.ajax = options => {
  const request = {options, done(fn) {this.success=fn; return this;}, fail(fn) {this.failure=fn; return this;}, always(fn) {this.complete=fn; return this;}};
  requests.push(request); return request;
};
function $(selector) {
  if (selector === document) return {ready: fn => fn()};
  return nodes[selector] || (nodes[selector] = {
    length: 1, value: '', attrs: {}, props: {}, events: {},
    val(v) {if (arguments.length) {this.value = v; return this;} return this.value;},
    attr(k,v) {if(arguments.length === 2) {this.attrs[k]=v; return this;} return this.attrs[k];},
    removeAttr(keys) {keys.split(' ').forEach(k => delete this.attrs[k]); return this;},
    data(k,v) {this.storage=this.storage||{}; if(arguments.length===2) {this.storage[k]=v; return this;} return this.storage[k];},
    remove() {this.removals = (this.removals || 0) + 1; return this;},
    toggle(v) {this.visible=v; return this;},
    after() {return this;}, html(v) {this.content=v; return this;},
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
vm.runInNewContext(fs.readFileSync('assets/js/ventas_pos/reporte_filtro_turno.js','utf8'), {$, document, window, clearTimeout: () => {pendingTimer=null;}, setTimeout: fn => {pendingTimer=fn; return 1;}});
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
search.val('2012'); search.events.input({type:'input'});
const removalsBeforeBlur = $('#lista_sugerencias').removals;
search.events.change({type:'change'});
assert.strictEqual($('#lista_sugerencias').removals, removalsBeforeBlur, 'Perder foco no debe retirar las sugerencias antes del clic');
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
assert.strictEqual(aborted, 0);
selectShift(); selectShift('2', '2026-09-16 08:00:00');
$('#limpiar_turno_resumen').events.click();
assertUnlocked(); assert.strictEqual(search.val(), '');
for (const event of ['change', 'comboboxselect']) {
  selectShift();
  if ($('#form_consulta').events[event]) { $('#form_consulta').events[event](); }
  assert.strictEqual($('#turno_operativo_id').val(), '1');
  assert.strictEqual(search.val(), 'T-1');
  assert.strictEqual($('#fecha_desde').props.disabled, true);
  assert.strictEqual($('#fecha_hasta').props.disabled, true);
}
selectShift(); search.val(''); search.events.input(); assertUnlocked();
window.ejecutar_acciones_con_item_sugerencia($('#item'), $('#other').attr('id','other'));
assert.strictEqual(forwarded, 1);
console.log('OK: autocomplete, intervalo nocturno, edición de búsqueda, cambio de filtros, limpieza y restauración de fechas.');

search.val('2026'); search.events.input({type:'input'}); pendingTimer();
assert.strictEqual(requests.length, 1);
assert.strictEqual($('#turno_busqueda_spinner').visible, true);
search.val('202609'); search.events.input({type:'input'}); pendingTimer();
assert.strictEqual(requests.length, 1, 'No consultar en paralelo');
requests[0].success('OBSOLETO'); requests[0].complete();
assert.strictEqual(requests.length, 2);
assert.notStrictEqual($('#lista_sugerencias').content, 'OBSOLETO');
requests[1].success('RESULTADO'); requests[1].complete();
assert.strictEqual($('#lista_sugerencias').content, 'RESULTADO');
assert.strictEqual($('#turno_busqueda_spinner').visible, false);
search.val('error'); search.events.input({type:'input'}); pendingTimer();
requests[2].failure(); requests[2].complete();
assert($('#periodo_turno_resumen').label.includes('Vuelva a escribir'));
assert.strictEqual($('#turno_busqueda_spinner').visible, false);
console.log('OK: consultas serializadas, respuestas obsoletas, spinner y errores de conexión.');
