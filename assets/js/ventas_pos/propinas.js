
function calcular_totales_propina() {

    var valor_total_factura = redondear_a_centena( parseFloat( $('#valor_total_factura').val()) + parseFloat( $('#valor_propina').val()) );

    var valor_propina = redondear_a_centena( $('#valor_propina').val() ) 

    $('#lbl_propina').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_propina ));
    
    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_total_factura ));

    // input hidden
    $('#aux_propina').val( valor_propina );
    $('#valor_total_factura').val( valor_total_factura );
}

function calcular_valor_a_pagar_propina (total_factura) {
    
    var valor_a_pagar_propina = redondear_a_centena( total_factura * $('#porcentaje_propina').val() / 100 );

    $('#lbl_propina').text('$ ' + valor_a_pagar_propina);
    $('#valor_propina').val(valor_a_pagar_propina);
    $('#aux_propina').val(valor_a_pagar_propina);
}

function reset_propina () {
    $('#lbl_propina').text('$ 0');
    $('#valor_propina').val(0);
    $('#aux_propina').val(0);

    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_sub_total_factura').val() ));
    $('#valor_total_factura').val( $('#valor_sub_total_factura').val() );

}

var motivos_registrados_lineas_medios_recaudos, cantidad_lineas_medios_recaudos, valor_total_propinas_en_lineas_de_pago;

function permitir_guardar_factura_con_propina () {
    
    var valor_total_lineas_medios_recaudos = parseFloat($('#total_valor_total').html().substring(1));

    if ( valor_total_lineas_medios_recaudos != 0) {
        
        cantidad_lineas_medios_recaudos = 0;
        valor_total_propinas_en_lineas_de_pago = 0;
        motivos_registrados_lineas_medios_recaudos = [];
        $('#ingreso_registros_medios_recaudo > tbody > tr').each(function( ){
            var array_celdas =  $(this).find('td');
            
            var text_motivo = array_celdas.eq(1).find('span').eq(0).text();
            
            var motivo_tesoreria_id =  parseInt( text_motivo.split('-')[0] );
            motivos_registrados_lineas_medios_recaudos.push(motivo_tesoreria_id);

            if ( parseInt( $('#motivo_tesoreria_propinas').val() ) == motivo_tesoreria_id )
            {
                var valor_linea = parseFloat( array_celdas.eq(4).text().substring(1) );
                valor_total_propinas_en_lineas_de_pago += valor_linea;     
            }
            
            cantidad_lineas_medios_recaudos++;
        });

        if ( cantidad_lineas_medios_recaudos > 1 ) { // Hay varias lineas de medios de recaudo
            
            if ( valor_total_propinas_en_lineas_de_pago != 0 && valor_total_propinas_en_lineas_de_pago != $('#valor_propina').val() ) {    
                Swal.fire({
                    icon: 'error',
                    title: 'Alerta!',
                    text: 'El Valor Ingresado de Propinas Recibidas en las líneas de medios de pago ($ ' + new Intl.NumberFormat("de-DE").format( valor_total_propinas_en_lineas_de_pago ) + ') NO es igual al Valor total de la Propina ($ ' + new Intl.NumberFormat("de-DE").format( $('#valor_propina').val() ) + ').'
                });
                return false;
            }

            if ( motivos_registrados_lineas_medios_recaudos.indexOf( parseInt( $('#motivo_tesoreria_propinas').val() ) ) >= 0  ) {
                // El motivo para propinas SI esta registrado en una linea de Pago.
                return true;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'Cuando ingresa VARIAS líneas de medios de pago, debe ingresar AL MENOS una línea con el Motivo Propinas Recibidas.'
            });
            return false;
        }else{

            if( existe_motivo_tesoreria_propinas() ){
                return true;
            }

            return false;
        }

    }else{ // Pago solo en efectivo
        
        if( existe_motivo_tesoreria_propinas() ){
            return true;
        }
        
        return false;
    }
}

function existe_motivo_tesoreria_propinas() {
    if ( $('#motivo_tesoreria_propinas').val() == '' || $('#motivo_tesoreria_propinas').val() == null || $('#motivo_tesoreria_propinas').val() == 0) {
        Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'No se ha definido un Motivo de Tesorería para las propinas. No podrá registrar propinas.'
            });
        
        return false;
    }

    return true;
}

function separar_json_linea_medios_recaudo(json_table2){

    if ($('#valor_propina').val() == 0) {
        return json_table2;
    }

    var new_json = JSON.parse(json_table2);
    if ( new_json.length > 1 ) {
        return json_table2;
    }

    var linea = new_json[0];

    var new_value =  Math.abs( parseFloat( linea.valor.substring(1) ) - $('#valor_propina').val() ) ;

    let teso_motivo_default_id = $( "#teso_motivo_default_id" ).val();
    let texto_motivo = get_text_from_select_for_value( teso_motivo_default_id );

    return '[{"teso_medio_recaudo_id":"' + linea.teso_medio_recaudo_id + '","teso_motivo_id":"' + teso_motivo_default_id + '-' + texto_motivo + '","teso_caja_id":"' + linea.teso_caja_id + '","teso_cuenta_bancaria_id":"' + linea.teso_cuenta_bancaria_id + '","valor":"$' + new_value + '"},{"teso_medio_recaudo_id":"' + linea.teso_medio_recaudo_id + '","teso_motivo_id":"' + $('#motivo_tesoreria_propinas').val() + '-' + $('#motivo_tesoreria_propinas_label').val() + '","teso_caja_id":"' + linea.teso_caja_id + '","teso_cuenta_bancaria_id":"' + linea.teso_cuenta_bancaria_id + '","valor":"$' + $('#valor_propina').val() + '"}]';
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
            reset_propina();
            calcular_totales_propina();
            $('#total_valor_total').actualizar_medio_recaudo();
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
        
        var valor_sub_total_factura = redondear_a_centena( $('#valor_sub_total_factura').val() )

        $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_sub_total_factura ));
        $('#valor_total_factura').val( valor_sub_total_factura );

        calcular_totales_propina();
        $('#total_valor_total').actualizar_medio_recaudo();
	});
    
    $('#remove_tip').on('click', function () {
        reset_propina();
        calcular_totales_propina();
        $('#total_valor_total').actualizar_medio_recaudo();
        $('#efectivo_recibido').select();
    });

    existe_motivo_tesoreria_propinas();
});