
function calcular_totales_datafono () {
    var valor_datafono = Math.round( parseFloat( $('#valor_datafono').val() ) || 0 );

    $('#lbl_datafono').text('$ ' + new Intl.NumberFormat("de-DE").format( valor_datafono ));

    // input hidden
    $('#aux_datafono').val( valor_datafono );
    pos_recalcular_total_con_recargos();
}

function calcular_valor_a_pagar_datafono (total_factura) {
    
    var valor_a_pagar_datafono = Math.round( total_factura * $('#porcentaje_datafono').val() / 100 );

    $('#lbl_datafono').text('$ ' + valor_a_pagar_datafono);
    $('#valor_datafono').val(valor_a_pagar_datafono);
    $('#aux_datafono').val(valor_a_pagar_datafono);
}

function reset_datafono () {
    $('#lbl_datafono').text('$ 0');
    $('#valor_datafono').val(0);
    $('#aux_datafono').val(0);

    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_sub_total_factura').val() ));
    $('#valor_total_factura').val( $('#valor_sub_total_factura').val() );
}

var motivos_registrados_lineas_medios_recaudos, cantidad_lineas_medios_recaudos;

function permitir_guardar_factura_con_datafono () {
    
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

            if( existe_motivo_tesoreria_datafono() ){
                return true;
            }

            return false;
        }

    }else{ // Pago solo en efectivo
        
        if( existe_motivo_tesoreria_datafono() ){
            return true;
        }
        
        return false;

    }
}

function existe_motivo_tesoreria_datafono() {
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

function separar_json_linea_medios_recaudo_datafono(json_table2){
    return pos_separar_recargo_medio_recaudo(
        json_table2,
        Math.round($('#valor_datafono').val()),
        $('#motivo_tesoreria_datafono').val(),
        $('#motivo_tesoreria_datafono_label').val()
    );
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
            
            calcular_valor_a_pagar_datafono(total_factura);
            
        }else{
            reset_datafono();
        }
        
        $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( $('#valor_sub_total_factura').val() ));
        $('#valor_total_factura').val( $('#valor_sub_total_factura').val() );

        calcular_totales_datafono();
        $('#total_valor_total').actualizar_medio_recaudo();
        $('#efectivo_recibido').select();

    });

    existe_motivo_tesoreria_datafono();
});
