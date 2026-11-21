const assert = require('node:assert/strict');
const fs = require('node:fs');
const {JSDOM} = require('jsdom');
const source = fs.readFileSync('/repo/assets/js/compras/confirmacion_documento.js', 'utf8');
async function mount(contado = true) {
    const dom = new JSDOM(`<!doctype html><form id="form_confirmar_documento"><input id="lineas_registros_medios_recaudo_confirmacion" value="0"></form>
    <button id="btn_confirmar_documento"></button><div id="modal_confirmar_documento" data-contado="${contado ? 1 : 0}" data-total="1000.10">
    <select id="confirmar_teso_medio_recaudo_id"><option value="1-Efectivo">Efectivo</option><option value="3-Tarjeta bancaria">Transferencia</option></select>
    <input type="hidden" id="confirmar_teso_motivo_id" value="17" data-label="Compras">
    <div id="confirmar_div_tipo_destino"><select id="confirmar_tipo_destino"><option value="caja">Caja</option><option value="cuenta">Banco</option></select></div>
    <div id="confirmar_div_caja"><select id="confirmar_teso_caja_id"><option value="1">Caja general</option><option value="99">Caja no permitida para este medio</option></select></div>
    <div id="confirmar_div_cuenta_bancaria"><select id="confirmar_teso_cuenta_bancaria_id"><option value="2">Banco</option></select></div>
    <input id="confirmar_valor_medio_pago"><button id="confirmar_agregar_pago"></button>
    <table id="confirmar_tabla_pagos"><tbody></tbody></table><span id="confirmar_total_pagos"></span><span id="confirmar_saldo_pagos"></span><div id="confirmar_error_pagos"></div>
    <button id="btn_confirmar_modal_accion"></button><i id="icon_confirmar_modal"></i><i id="icon_confirmar_modal_spin"></i><span id="texto_confirmar_modal"></span></div>
    <script type="application/json" id="confirmar_destinos_json">{"activo":true,"destinos":{"1":{"cajas":[{"id":1}],"cuentas":[]},"3":{"cajas":[],"cuentas":[{"id":2}]}}}</script>`, {runScripts:'outside-only'});
    const win = dom.window; const $ = require('jquery')(win); win.jQuery = $;
    $.fn.modal = function () { return this; };
    win.eval(source);
    await new Promise(resolve => setTimeout(resolve, 20));
    let enviados = 0;
    $('#form_confirmar_documento').on('submit.test', function (e) { if (!e.isDefaultPrevented()) enviados++; e.preventDefault(); });
    return {dom, $, enviados: () => enviados};
}
(async () => {
    const {dom, $, enviados} = await mount();
    assert.equal($('#confirmar_valor_medio_pago').val(), '1000.10');
    assert.equal($('#confirmar_teso_caja_id option').length, 1, 'Filtra destinos configurados');
    for (const valor of ['-1', 'NaN', '1.001', '2000']) {
        $('#confirmar_valor_medio_pago').val(valor); $('#confirmar_agregar_pago').trigger('click');
        assert.equal($('#confirmar_tabla_pagos tbody tr').length, 0);
    }
    $('#confirmar_valor_medio_pago').val('400,05'); $('#confirmar_agregar_pago').trigger('click');
    assert.equal($('#confirmar_valor_medio_pago').val(), '600.05');
    $('#btn_confirmar_modal_accion').trigger('click');
    assert.equal(enviados(), 0, 'No envía con saldo pendiente');
    $('#confirmar_teso_medio_recaudo_id').val('3-Tarjeta bancaria').trigger('change');
    assert.equal($('#confirmar_tipo_destino').val(), 'cuenta');
    $('#confirmar_valor_medio_pago').trigger($.Event('keydown', {which:13}));
    assert.equal($('#confirmar_tabla_pagos tbody tr').length, 2);
    $('#confirmar_tabla_pagos .confirmar_quitar_pago').first().trigger('click');
    assert.equal($('#confirmar_valor_medio_pago').val(), '400.05');
    $('#confirmar_teso_medio_recaudo_id').val('1-Efectivo').trigger('change');
    $('#confirmar_agregar_pago').trigger('click');
    $('#btn_confirmar_documento').trigger('click');
    assert.equal($('#confirmar_tabla_pagos tbody tr').length, 2, 'Reabrir conserva los pagos');
    $('#btn_confirmar_modal_accion').trigger('click');
    const pagos = JSON.parse($('#lineas_registros_medios_recaudo_confirmacion').val());
    assert.equal(pagos.length, 2, 'No envía fila de totales');
    assert.equal(pagos[0].teso_cuenta_bancaria_id, 2); assert.equal(pagos[0].teso_caja_id, 0);
    assert.equal(pagos[1].teso_caja_id, 1); assert.equal(pagos[1].valor_recaudo, '400.05');
    assert.equal(enviados(), 1); $('#btn_confirmar_modal_accion').trigger('click'); assert.equal(enviados(), 1, 'Evita doble envío');
    dom.window.close();
    const credito = await mount(false); credito.$('#btn_confirmar_modal_accion').trigger('click');
    assert.equal(credito.enviados(), 1, 'Crédito confirma sin capturar pagos'); credito.dom.window.close();
    console.log('OK: pagos mixtos, centavos, agregar/eliminar, destinos, saldo, crédito y envío único.');
})().catch(e => { console.error(e); process.exit(1); });
