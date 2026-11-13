// Solo se carga en create/edit de facturas POS.
function pos_cantidad_unitaria_activa() {
    return $('#pos_ocultar_cantidad').val() === '1';
}
function pos_preparar_cantidad_nueva() {
    if (!pos_cantidad_unitaria_activa()) { return; }
    cantidad = 1;
    $('#cantidad').val(1);
}
function pos_enfocar_precio_unitario() {
    pos_preparar_cantidad_nueva();
    calcular_valor_descuento();
    calcular_impuestos();
    calcular_precio_total();
    $('#precio_unitario').focus().select();
}
$(document).ready(function () {
    if (!pos_cantidad_unitaria_activa()) { return; }
    var table = document.getElementById('ingreso_registros');
    if (!table) { return; }
    function ocultarCantidad() {
        // Conservar celdas y su orden: cálculo y serialización usan esos índices.
        $(table).find('tr').each(function () {
            var row = $(this);
            row.find('#cantidad, .quantity').closest('td').addClass('pos-cantidad-oculta');
            if (!row.find('.quantity').length) { row.find('.elemento_modificar').first().filter(function () {
                return $(this).attr('id') !== 'elemento_modificar_precio_unitario';
            }).closest('td').addClass('pos-cantidad-oculta'); }
        });
        $(table).find('th').filter(function () {
            return $(this).text().trim().toLowerCase() === 'cantidad';
        }).addClass('pos-cantidad-oculta');
        $('#cantidad').prop('readonly', true).attr('tabindex', '-1');
    }
    ocultarCantidad();
    new MutationObserver(ocultarCantidad).observe(table, {childList: true, subtree: true});
    pos_preparar_cantidad_nueva();
});
