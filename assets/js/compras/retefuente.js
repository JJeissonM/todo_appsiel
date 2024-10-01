function aplicar_retefuente(recalcular)
{	
    if ( $('#subtotal').text() == ' $ 0 ' || $('#subtotal').text() == '$ 0' ) {
        $('#lbl_total_retefuente').text( '$ 0' );
        $('#valor_total_retefuente').val(0);
        return false;
    }

    if ( $('#tasa_retefuente option:selected').text() == '' ) {
        $('#lbl_total_retefuente').text( '$ 0' );
        $('#valor_total_retefuente').val(0);

        if ( recalcular ) {
            calcular_totales()
        }

        return false;
    }

    if ( recalcular ) {
        calcular_totales()
    }
    
    var subtotal = get_valor_formateado_en_float( $('#subtotal').text() );

    var total_descuento = get_valor_formateado_en_float( $('#descuento').text() );     

    // subtotal = base_impuesto + total_descuento, entonces
    var base_impuesto = subtotal - total_descuento

    var tasa_retefuente = parseFloat( $('#tasa_retefuente option:selected').text() );

    var valor_total_retefuente = base_impuesto * tasa_retefuente / 100;
    
    $('#lbl_total_retefuente').text( '-$ ' + new Intl.NumberFormat("de-DE").format( valor_total_retefuente.toFixed(2) ) );

    // Para el formulario que se envia
    $('#valor_total_retefuente').val(valor_total_retefuente);
    $('#retencion_id').val( $('#tasa_retefuente').val() );

    // Recalcular total factura
	var total_factura = get_valor_formateado_en_float( $('#total_factura').text() );
    $('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( ( total_factura - valor_total_retefuente ).toFixed(2) ) );
}

// Se reemplaza varias veces el "." por vacio, y luego la coma por punto
function get_valor_formateado_en_float( texto )
{
    return parseFloat( texto.substring(1).replace(".","").replace(".","").replace(".","").replace(".","").replace(",",".") );
}

$(document).ready(function(){

    $('#tasa_retefuente').parent('div').prev('label').hide();

    $('#btn_add_retefuente').click(function (event) {
        event.preventDefault();

        $(this).hide()
        $('#select_tasa_retefuente').fadeIn(1000)
        $('#btn_cancel_retefuente').fadeIn(1000)        
        
    });

    $('#btn_cancel_retefuente').click(function (event) {
        event.preventDefault();

        $('#select_tasa_retefuente').hide()
        $('#btn_cancel_retefuente').hide()        
        
        $('#btn_add_retefuente').fadeIn(1000)  
        $('#tasa_retefuente').val('')

        aplicar_retefuente( true )
    });

    $("#select_tasa_retefuente").on("change", function () {
        aplicar_retefuente( true );
    });

});