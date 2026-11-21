(function (window, $) {
    'use strict';
    $(function () {
        var modal = $('#modal_confirmar_documento');
        if (!modal.length) return;
        var contado = modal.attr('data-contado') === '1';
        var neto = Math.round(Number(modal.attr('data-total')) * 100);
        var pagos = [], enviando = false, destinos = {activo: false, destinos: {}};
        try { destinos = JSON.parse($('#confirmar_destinos_json').text() || '{}'); } catch (ignore) {}
        var cajas = $('#confirmar_teso_caja_id option').clone();
        var cuentas = $('#confirmar_teso_cuenta_bancaria_id option').clone();
        function id(selector) { return Number(($(selector).val() || '0').split('-')[0]); }
        function texto(selector) { return $(selector + ' option:selected').text(); }
        function dinero(centavos) { return '$' + (centavos / 100).toLocaleString('es-CO', {minimumFractionDigits: 2, maximumFractionDigits: 2}); }
        function total() { return pagos.reduce(function (sum, p) { return sum + p.centavos; }, 0); }
        function error(message) { $('#confirmar_error_pagos').text(message).show(); return false; }
        function opciones(selector, originales, permitidos) {
            var previo = $(selector).val();
            var lista = originales.filter(function () { return !destinos.activo || permitidos.some(function (p) { return String(p.id) === String($(this).val()); }, this); });
            $(selector).empty().append(lista.clone());
            if ($(selector).find('option').filter(function () { return $(this).val() === previo; }).length) $(selector).val(previo);
        }
        function destino() {
            var medium = ($('#confirmar_teso_medio_recaudo_id').val() || '').split('-');
            var mapa = (destinos.destinos || {})[medium[0]] || {cajas: [], cuentas: []};
            opciones('#confirmar_teso_caja_id', cajas, mapa.cajas);
            opciones('#confirmar_teso_cuenta_bancaria_id', cuentas, mapa.cuentas);
            var puedeCaja = $('#confirmar_teso_caja_id option').length > 0;
            var puedeCuenta = $('#confirmar_teso_cuenta_bancaria_id option').length > 0;
            var tipo = $('#confirmar_tipo_destino').val();
            if (medium[1] === 'Efectivo') tipo = 'caja';
            else if (medium[1] === 'Tarjeta bancaria') tipo = 'cuenta';
            else if (!puedeCaja) tipo = 'cuenta';
            else if (!puedeCuenta) tipo = 'caja';
            $('#confirmar_tipo_destino').val(tipo);
            $('#confirmar_div_tipo_destino').toggle(medium[1] !== 'Efectivo' && medium[1] !== 'Tarjeta bancaria' && puedeCaja && puedeCuenta);
            $('#confirmar_div_caja').toggle(tipo === 'caja');
            $('#confirmar_div_cuenta_bancaria').toggle(tipo === 'cuenta');
        }
        function render() {
            var body = $('#confirmar_tabla_pagos tbody').empty();
            pagos.forEach(function (p, i) {
                var tr = $('<tr>');
                [p.medio, p.motivo, p.destino, dinero(p.centavos)].forEach(function (value, n) {
                    $('<td>').toggleClass('text-right', n === 3).text(value).appendTo(tr);
                });
                $('<td>').append($('<button type="button" class="btn btn-danger btn-xs confirmar_quitar_pago" aria-label="Eliminar pago">').attr('data-index', i).text('Eliminar')).appendTo(tr);
                body.append(tr);
            });
            $('#confirmar_total_pagos').text(dinero(total()));
            $('#confirmar_saldo_pagos').text(dinero(neto - total()));
            $('#confirmar_valor_medio_pago').val(neto > total() ? ((neto - total()) / 100).toFixed(2) : '');
            $('#confirmar_error_pagos').hide();
        }
        function agregar() {
            var raw = $.trim($('#confirmar_valor_medio_pago').val() || '');
            if (!/^\d+(?:[.,]\d{1,2})?$/.test(raw)) return error('Ingrese un valor positivo con máximo dos decimales.');
            var centavos = Math.round(Number(raw.replace(',', '.')) * 100);
            if (!isFinite(centavos) || centavos <= 0) return error('Ingrese un valor mayor que cero.');
            if (total() + centavos > neto) return error('Los pagos superan el valor neto a pagar.');
            var caja = $('#confirmar_tipo_destino').val() === 'caja';
            if (!id('#confirmar_teso_medio_recaudo_id') || !id('#confirmar_teso_motivo_id')) return error('Seleccione un medio de pago y verifique el motivo de compras de contado en la configuración de tesorería.');
            if (!id(caja ? '#confirmar_teso_caja_id' : '#confirmar_teso_cuenta_bancaria_id')) return error('Seleccione una caja o cuenta bancaria válida.');
            pagos.push({
                medio: texto('#confirmar_teso_medio_recaudo_id'), motivo: $('#confirmar_teso_motivo_id').attr('data-label') || '',
                destino: texto(caja ? '#confirmar_teso_caja_id' : '#confirmar_teso_cuenta_bancaria_id'), centavos: centavos,
                datos: {teso_medio_recaudo_id: id('#confirmar_teso_medio_recaudo_id'), teso_motivo_id: id('#confirmar_teso_motivo_id'),
                    teso_caja_id: caja ? id('#confirmar_teso_caja_id') : 0, teso_cuenta_bancaria_id: caja ? 0 : id('#confirmar_teso_cuenta_bancaria_id'),
                    valor_recaudo: (centavos / 100).toFixed(2)}
            });
            render(); return true;
        }
        $('#btn_confirmar_documento').on('click.compraConfirmacion', function (e) { e.preventDefault(); destino(); modal.modal({backdrop: 'static'}); });
        $('#confirmar_teso_medio_recaudo_id, #confirmar_tipo_destino').on('change.compraConfirmacion', destino);
        $('#confirmar_agregar_pago').on('click.compraConfirmacion', agregar);
        $('#confirmar_valor_medio_pago').on('keydown.compraConfirmacion', function (e) { if (e.which === 13 || e.key === 'Enter') { e.preventDefault(); agregar(); } });
        $('#confirmar_tabla_pagos').on('click.compraConfirmacion', '.confirmar_quitar_pago', function () { pagos.splice(Number($(this).attr('data-index')), 1); render(); });
        $('#btn_confirmar_modal_accion').on('click.compraConfirmacion', function (e) { e.preventDefault(); $('#form_confirmar_documento').trigger('submit'); });
        $('#form_confirmar_documento').on('submit.compraConfirmacion', function (e) {
            if (enviando) { e.preventDefault(); return; }
            if (contado) {
                if (!pagos.length && !agregar()) { e.preventDefault(); return; }
                if (total() !== neto) { e.preventDefault(); error('Agregue los pagos hasta completar el valor neto a pagar.'); return; }
                $('#lineas_registros_medios_recaudo_confirmacion').val(JSON.stringify(pagos.map(function (p) { return p.datos; })));
            }
            enviando = true;
            $('#btn_confirmar_modal_accion').prop('disabled', true);
            $('#icon_confirmar_modal').hide(); $('#icon_confirmar_modal_spin').show(); $('#texto_confirmar_modal').text('Procesando...');
        });
        if (contado) { destino(); render(); }
    });
})(window, window.jQuery);
