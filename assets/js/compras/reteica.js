function validar_base_reteica() {
    var valor = $('#reteica_base').val();
    if (!/^\d+(\.\d{1,2})?$/.test(valor) || !isFinite(Number(valor)) || Number(valor) > 9999999999999.99) {
        alert('Ingrese una base de ReteICA mayor o igual a cero, con máximo dos decimales.');
        $('#reteica_base').focus();
        return false;
    }
    return true;
}
/* Tarifa persistida en porcentaje; presentación en por mil. */
function calcular_reteica(base) {
    var tasa = parseFloat($('#reteica_select option:selected').attr('data-tasa')) || 0;
    if ($('#reteica_base_manual').val() === '1') {
        base = Number($('#reteica_base').val());
    } else {
        $('#reteica_base').val((Math.max(0, base)).toFixed(2));
    }
    if (!isFinite(base) || base < 0) base = 0;
    base = Math.max(0, Math.round(base * 100) / 100);
    var valor = Math.round((base * tasa / 100 + 1e-9) * 100) / 100;
    $('#reteica_preview').val(valor.toFixed(2));
    $('#reteica_base_preview').text('$ ' + new Intl.NumberFormat('de-DE').format(base));
    $('#reteica_importe').text(tasa ? '-$ ' + new Intl.NumberFormat('de-DE').format(valor) : '');
    $('#reteica_etiqueta').text(tasa ? 'Ret. ICA (' + (tasa * 10).toFixed(2) + ' por mil)' : 'Ret. ICA');
    return valor;
}
$(function () {
    function editar() {
        $('#reteica_editor, #reteica_confirmar, #reteica_reset').show();
        $('#reteica_add, #reteica_editar, #reteica_importe').hide();
        // Una selección en edición debe confirmarse antes de enviar la compra.
        $('#reteica_retencion_id').val(0);
        $('#reteica_select').focus();
        calcular_totales();
    }
    $('#reteica_add, #reteica_editar').on('click', editar);
    $('#reteica_select').on('change', calcular_totales);
    $('#reteica_base').on('input', function () {
        $('#reteica_base_manual').val(1);
        calcular_totales();
    });
    $('#reteica_confirmar').on('click', function () {
        if (!validar_base_reteica()) return;
        if ($('#reteica_select').val() == '0') { $('#reteica_select').focus(); return; }
        $('#reteica_retencion_id').val($('#reteica_select').val());
        $('#reteica_editor, #reteica_confirmar, #reteica_add').hide();
        $('#reteica_editar, #reteica_reset, #reteica_importe').show();
        calcular_totales();
    });
    $('#reteica_reset').on('click', function () {
        $('#reteica_retencion_id, #reteica_select, #reteica_base_manual').val(0);
        $('#reteica_editor, #reteica_confirmar, #reteica_editar, #reteica_reset').hide();
        $('#reteica_add, #reteica_importe').show();
        calcular_totales();
    });
    // El guardado usa form.submit(), por eso se valida también el botón existente.
    var boton = document.getElementById('btn_guardar');
    function validar(event) {
        if ($('#reteica_editor').is(':visible') || ($('#reteica_retencion_id').val() > 0 && !validar_base_reteica())) {
            event.preventDefault();
            event.stopImmediatePropagation();
            alert('Confirme o elimine la edición de ReteICA antes de guardar.');
        }
    }
    if (boton) { boton.addEventListener('click', validar, true); }
    var formulario = document.getElementById('form_create');
    if (formulario) { formulario.addEventListener('submit', validar, true); }
});
