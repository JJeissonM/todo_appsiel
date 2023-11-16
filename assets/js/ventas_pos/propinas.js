
$.fn.calcular_totales_propina = function () {

    var valor_total_factura = parseFloat($('#valor_sub_total_factura').val()) + parseFloat( $('#valor_propina').val());

    $('#lbl_propina').text('$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_propina').val() ));
    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_total_factura ));

    // input hidden
    $('#aux_propina').val( $('#valor_propina').val() );
    $('#valor_total_factura').val( valor_total_factura );
}

$.fn.calcular_valor_a_pagar_propina = function (total_factura) {
    
    var valor_a_pagar_propina = total_factura * $('#porcentaje_propina').val() / 100;

    $('#lbl_propina').text('$ ' + valor_a_pagar_propina);
    $('#valor_propina').val(valor_a_pagar_propina);
    $('#aux_propina').val(valor_a_pagar_propina);
}

$.fn.reset_propina = function () {
    $('#lbl_propina').text('$ 0');
    $('#valor_propina').val(0);
    $('#aux_propina').val(0);
}

$(document).ready(function () {

    $('#teso_medio_recaudo_id_propina').val('1-Efectivo');
    $('#teso_caja_id_propina').val( $('#caja_pdv_default_id').val() );
    
    $('#teso_medio_recaudo_id_propina').on('change', function () {
        if ( $(this).val() != '1-Efectivo' ) {
            $('#div_caja_propina').hide();
            $('#div_banco_propina').show();
        }else{
            $('#div_caja_propina').show();
            $('#div_banco_propina').hide();
        }
    });
    
    $('#valor_propina').on('click', function (event) {
        $(this).select();
    });

    $('#valor_propina').on('keyup', function (event) {

		var codigo_tecla_presionada = event.which || event.keyCode;

        if ($(this).val() == '') {
            $.fn.reset_propina();
            $.fn.calcular_totales_propina();
            $(this).select();
        }

        var init_value = parseFloat( $('#aux_propina').val() );
        if (!$.isNumeric( $(this).val() ) ) {
            $(this).val( init_value );
            return false;
        }

		switch (codigo_tecla_presionada) {
			case 13:// Al presionar Enter
				$('#efectivo_recibido').select();
				break;

			default:
				// 
				break;
		}

        $.fn.calcular_totales_propina();
	});
    
    $('#remove_tip').on('click', function () {
        $.fn.reset_propina();
        $.fn.calcular_totales_propina();
        $('#efectivo_recibido').select();
    });

    if ( $('#motivo_tesoreria_propinas').val() == '' || $('#motivo_tesoreria_propinas').val() == null || $('#motivo_tesoreria_propinas').val() == 0) {
        Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'No se ha definido un Motivo de Tesorería para las propinas. No podrá registrar propinas.'
            });
    }
});