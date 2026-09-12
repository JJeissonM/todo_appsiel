const fs = require('fs');
const vm = require('vm');
const assert = require('assert');
const values = {'#teso_motivo_default_id': '83', '#valor_sub_total_factura': 178595, '#valor_propina': 8900, '#valor_datafono': 8930};
const context = {console, Intl, redondear_centena: 1,
  $: selector => ({val(v) {if (arguments.length) values[selector] = v; return values[selector];}, text() {}}),
  get_text_from_select_for_value: () => 'Ventas'};
context.$.extend = (...args) => Object.assign(...args);
vm.createContext(context);
function include(file, name) {
  const source = fs.readFileSync(file, 'utf8');
  const start = source.indexOf('function ' + name + '(');
  const alternative = source.indexOf('function ' + name + ' (');
  const from = start < 0 ? alternative : start;
  assert(from >= 0);
  vm.runInContext(source.slice(from, source.indexOf('\nfunction ', from + 1)), context);
}
include('assets/js/ventas_pos/facturas.js', 'redondear_a_centena');
include('assets/js/ventas_pos/commons.js', 'pos_recalcular_total_con_recargos');
include('assets/js/ventas_pos/commons.js', 'pos_separar_recargo_medio_recaudo');
const result = context.pos_recalcular_total_con_recargos();
assert.strictEqual(result.total_redondeado, 196400);
assert.strictEqual(result.ajuste, -25);
context.redondear_centena = 0;
values['#valor_sub_total_factura'] = 178594.6;
assert.strictEqual(context.pos_recalcular_total_con_recargos().ajuste, 0.4);
let input = JSON.stringify([
  {teso_motivo_id: '85-Propina', valor: '$8900', teso_caja_id: '1-Caja'},
  {teso_motivo_id: '83-Ventas', valor: '$187500', teso_caja_id: '0-', teso_cuenta_bancaria_id: '11-Banco'}
]);
const separated = context.pos_separar_recargo_medio_recaudo(input, 8930, 84, 'Comision');
const lines = JSON.parse(separated);
assert.strictEqual(lines[0].valor, '$8900');
assert.strictEqual(lines[1].valor, '$178570');
assert.strictEqual(lines[2].valor, '$8930');
assert.strictEqual(lines[2].teso_cuenta_bancaria_id, '11-Banco');
assert.strictEqual(context.pos_separar_recargo_medio_recaudo(separated, 8930, 84, 'Comision'), separated);
const onlyTip = JSON.stringify([{teso_motivo_id: '85-Propina', valor: '$20000'}]);
assert.strictEqual(context.pos_separar_recargo_medio_recaudo(onlyTip, 8930, 84, 'Comision'), onlyTip);
// La comisión también es válida si ocupa la primera fila de pagos.
include('assets/js/ventas_pos/datafono.js', 'permitir_guardar_factura_con_datafono');
context.$ = selector => {
  if (selector === '#total_valor_total') return {html: () => '$196400'};
  if (selector === '#motivo_tesoreria_datafono') return {val: () => '84'};
  if (selector === '#ingreso_registros_medios_recaudo > tbody > tr') return {
    each: callback => ['84-Comision', '83-Ventas'].forEach(motive => callback.call({motive}))
  };
  return {find: () => ({eq: () => ({find: () => ({eq: () => ({text: () => selector.motive})})})})};
};
assert.strictEqual(context.permitir_guardar_factura_con_datafono(), true);
console.log('OK: redondeo único, recargos separados, destinos conservados y datáfono en primera fila.');
