
$.fn.calcular_totales_datafono = function () {
    
    var valor_datafono = Math.round( parseFloat( $('#valor_datafono').val() ) );

    var valor_total_factura = parseFloat( $('#valor_sub_total_factura').val() ) + Math.round(  valor_datafono );

    $('#lbl_datafono').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_datafono ));
    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_total_factura ));

    // input hidden
    $('#aux_datafono').val( Math.round( valor_datafono ) );
    $('#valor_total_factura').val( valor_total_factura );
}

$.fn.calcular_valor_a_pagar_datafono = function (total_factura) {
    
    var valor_a_pagar_datafono = Math.round( total_factura * $('#porcentaje_datafono').val() / 100 );

    $('#lbl_datafono').text('$ ' + valor_a_pagar_datafono);
    $('#valor_datafono').val(valor_a_pagar_datafono);
    $('#aux_datafono').val(valor_a_pagar_datafono);
}

$.fn.reset_datafono = function () {
    $('#lbl_datafono').text('$ 0');
    $('#valor_datafono').val(0);
    $('#aux_datafono').val(0);
}

var motivos_registrados_lineas_medios_recaudos, cantidad_lineas_medios_recaudos;

$.fn.permitir_guardar_factura_con_datafono = function () {
    
    var valor_total_lineas_medios_recaudos = parseFloat($('#total_valor_total').html().substring(1));

    if ( valor_total_lineas_medios_recaudos != 0) {
        
        cantidad_lineas_medios_recaudos = 0;
        motivos_registrados_lineas_medios_recaudos = [];
        $('#ingreso_registros_medios_recaudo > tbody > tr').each(function( ){
            var array_celdas =  $(this).find('td');
            
            var text_motivo = array_celdas.eq(1).find('span').eq(0).text();
            
            var motivo_tesoreria_id =  parseInt( text_motivo.split('-')[0] );
            motivos_registrados_lineas_medios_recaudos.push(motivo_tesoreria_id);
            cantidad_lineas_medios_recaudos++;
        });

        if ( cantidad_lineas_medios_recaudos > 1 ) { // Hay varias lineas medios de recaudo
            
            if ( motivos_registrados_lineas_medios_recaudos.indexOf( parseInt( $('#motivo_tesoreria_datafono').val() ) ) > 0  ) {
                // El motivo para datafono esta registrado en una linea de Pago.
                return true;
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'Cuando ingresa VARIAS líneas de medios de pago, debe ingresar AL MENOS una línea con el Motivo para datafono.'
            });
            return false;
        }else{

            if( $.fn.existe_motivo_tesoreria_datafono() ){
                return true;
            }

            return false;
        }

    }else{ // Pago solo en efectivo
        
        if( $.fn.existe_motivo_tesoreria_datafono() ){
            return true;
        }
        
        return false;

    }
}

$.fn.existe_motivo_tesoreria_datafono = function() {
    if ( $('#motivo_tesoreria_datafono').val() == '' || $('#motivo_tesoreria_datafono').val() == null || $('#motivo_tesoreria_datafono').val() == 0) {
        Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'No se ha definido un Motivo de Tesorería para las datafono. No podrá registrar datafono.'
            });
        
        return false;
    }

    return true;
}

$.fn.separar_json_linea_medios_recaudo = function(json_table2){

    var valor_datafono = Math.round( $('#valor_datafono').val() );

    if ( valor_datafono == 0) {
        return json_table2;
    }

    var new_json = JSON.parse(json_table2);
    if ( new_json.length > 1 ) {
        return json_table2;
    }

    var linea = new_json[0];

    var new_value =  Math.abs( parseFloat( linea.valor.substring(1) ) - valor_datafono ) ;

    return '[{"teso_medio_recaudo_id":"' + linea.teso_medio_recaudo_id + '","teso_motivo_id":"1-Recaudo clientes","teso_caja_id":"' + linea.teso_caja_id + '","teso_cuenta_bancaria_id":"' + linea.teso_cuenta_bancaria_id + '","valor":"$' + new_value + '"},{"teso_medio_recaudo_id":"' + linea.teso_medio_recaudo_id + '","teso_motivo_id":"' + $('#motivo_tesoreria_datafono').val() + '-' + $('#motivo_tesoreria_datafono_label').val() + '","teso_caja_id":"' + linea.teso_caja_id + '","teso_cuenta_bancaria_id":"' + linea.teso_cuenta_bancaria_id + '","valor":"$' + valor_datafono + '"}]';
}

$(document).ready(function () {

    $('#teso_medio_recaudo_id_datafono').val('1-Efectivo');
    $('#teso_caja_id_datafono').val( $('#caja_pdv_default_id').val() );
    
    $('#teso_medio_recaudo_id_datafono').on('change', function () {
        if ( $(this).val() != '1-Efectivo' ) {
            $('#div_caja_datafono').hide();
            $('#div_banco_datafono').show();
        }else{
            $('#div_caja_datafono').show();
            $('#div_banco_datafono').hide();
        }
    });

    $(document).on('change', '#calcular_comision_datafono', function() {

        if( this.checked) {
            
            $.fn.calcular_valor_a_pagar_datafono(total_factura);
            
        }else{
            $.fn.reset_datafono();
        }

        $.fn.calcular_totales_datafono();
        $('#total_valor_total').actualizar_medio_recaudo();
        $('#efectivo_recibido').select();

    });

    $.fn.existe_motivo_tesoreria_datafono();
});