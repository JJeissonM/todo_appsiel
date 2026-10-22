const fs = require('fs'), vm = require('vm'), assert = require('assert');
let list, closeClick, selectClick, selected = false, callbackInput;
const hidden = {val(value) {this.value = value;}};
const input = {
  attrs: {}, attr(key, value) {if(arguments.length === 2) {this.attrs[key] = value; return this;} return this.attrs[key];},
  val(value) {this.value = value; return this;}, hasClass() {return true;}, css() {}, next() {return hidden;}
};
const group = {parent() {return {prev() {return input;}};}};
const item = {
  attr(key) {return {'data-registro_id':'2012', 'data-turno-estado':'CERRADO'}[key];},
  text() {return 'T-2012 | CERRADO';}, parent() {return group;}
};
const child = {};
const document = {
  addEventListener(event, callback) {closeClick = callback;},
  getElementById() {return list;}
};
function $(element) {
  if(element === document) return {
    ready(callback) {callback();},
    on(event, selector, callback) {if(event === 'click') selectClick = callback;}
  };
  if(element === '#lista_sugerencias' || element === list) return {remove() {list = null;}};
  return element;
}
$.trim = value => value.trim();
vm.runInNewContext(fs.readFileSync('assets/js/input_lista_sugerencias.js','utf8'), {
  $, document, ejecutar_acciones_con_item_sugerencia(result, field) {selected = result === item; callbackInput = field;}
});
function createList() {
  list = {contains(target) {return target === item || target === child;}, getAttribute() {return 'turno_operativo_busqueda';}};
}
for (const target of [item, child]) {
  createList();
  // El listener nativo corre antes del clic delegado de jQuery.
  closeClick({target});
  assert(list, 'La sugerencia debe seguir en el DOM para recibir el clic delegado');
  selectClick.call(item);
  assert.strictEqual(hidden.value, '2012');
  assert.strictEqual(input.value, 'T-2012 | CERRADO');
  assert.strictEqual(input.attrs['data-turno-state'], 'CERRADO');
  assert.strictEqual(callbackInput, input);
  assert(selected); assert.strictEqual(list, null);
}
createList(); closeClick({target:{id:'turno_operativo_busqueda'}}); assert(list);
closeClick({target:{id:'otro_campo'}}); assert.strictEqual(list, null);
console.log('OK: clic en sugerencia y contenido, ID seleccionado, callback y cierre fuera de la lista.');
