const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const source = fs.readFileSync('assets/js/ventas_pos/commons.js', 'utf8');
const values = { '#uniqid': 'venta-original' };
let generated = 0;
const context = {
  $: selector => ({
    val(value) { if (arguments.length) { values[selector] = value; return this; } return values[selector]; },
    remove() {},
    tableToJSON() { return [{ inv_producto_id: 1, precio_total: 27000 }]; },
    serialize() { return values['#uniqid']; }
  }),
  pos_get_manejo_recargos_flags: () => ({}),
  pos_validar_y_recalcular_lineas_registros: () => true,
  get_json_registros_medios_recaudo: () => '[]',
  update_uniqid: () => { values['#uniqid'] = 'nueva-' + ++generated; },
  update_request_id() {},
  pos_regenerar_uniqid_para_reintento() { throw Error('No debe regenerar después de un error'); },
  Swal: { fire() {} }
};
context.$.trim = s => s.trim();
vm.createContext(context);
for (const name of ['pos_preparar_payload_guardado', 'pos_reset_contexto_despues_guardado', 'pos_mostrar_mensaje_error_guardado']) {
  const start = source.indexOf('function ' + name + '(');
  const end = source.indexOf('\nfunction ', start + 1);
  vm.runInContext(source.slice(start, end), context);
}
const original = context.pos_preparar_payload_guardado().data;
for (const status of [0, 422, 500, 409]) {
  context.pos_mostrar_mensaje_error_guardado({status, responseJSON: {message: 'Error'}});
  assert.strictEqual(context.pos_preparar_payload_guardado().data, original);
}
context.pos_reset_contexto_despues_guardado();
assert.notStrictEqual(context.pos_preparar_payload_guardado().data, original);
assert.strictEqual(generated, 1);
console.log('OK: reintentos conservan la venta; guardado confirmado inicia una nueva.');
