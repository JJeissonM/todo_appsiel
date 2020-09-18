
/*
			**	Abrir formulario de medios de pago
			*/
$("#btn_nuevo").click(function(event){
    event.preventDefault();
    if (validar_requeridos()) {
        //$('#div_ingreso_registros_medios_recaudo').show();
        reset_form_registro();
        $("#recaudoModal").modal(
            {backdrop: "static",keyboard: 'true'}
        );
    }
});

// Al mostrar la ventana modal
$("#recaudoModal,#myModal2").on('shown.bs.modal', function () {
    $('#teso_medio_recaudo_id').focus();
});
// Al OCULTAR la ventana modal
$("#recaudoModal,#myModal2").on('hidden.bs.modal', function () {
    $('#btn_continuar2').focus();
});

$('#teso_medio_recaudo_id').change(function(){
    var valor = $(this).val().split('-');

    if ( valor == '' )
    {
        $('#div_cuenta_bancaria').hide();
        $('#div_caja').hide();
        deshabilitar_text($('#valor_total'));
        $(this).focus();
        alert('Debe escoger un medio de recaudo');
        return false;
    }

    var texto_motivo = $( "#teso_motivo_id" ).html();//[ , $( "#teso_motivo_id option:selected" ).text() ];

    if (texto_motivo == '')
    {
        alert('No se han creado motivos para el TIPO DE RECAUDO selecccionado. Debe crear al menos un MOTIVO para cada TIPO DE RECAUDO. No puede continuar.');
        $('#teso_tipo_motivo').focus();
    }else{

        $('#div_cuenta_bancaria').hide();
        $('#div_caja').show();

        if ( valor[1] == 'Tarjeta bancaria' )
        {
            $('#div_caja').hide();
            $('#div_cuenta_bancaria').show();
        }

        habilitar_text($('#valor_total'));
        $('#valor_total').focus();

    }

});

$('#valor_total').keyup(function(event){
    /**/
    var ok;
    if( $.isNumeric( $(this).val() ) ) {
        $(this).attr('style','background-color:white;');
        ok = true;
    }else{
        $(this).attr('style','background-color:#FF8C8C;');
        $(this).focus();
        ok = false;
    }

    var x = event.which || event.keyCode;
    if( x === 13 ){

        if (ok) {
            $('#btn_agregar').show();
            $('#btn_agregar').focus();
        }

    }
});

/*
    ** Al presionar el botón agregar (ingreso de medios de recaudo)
    */
$('#btn_agregar').click(function(event){
    event.preventDefault();

    var valor_total = $('#valor_total').val();

    if($.isNumeric(valor_total) && valor_total>0)
    {

        var medio_recaudo = $( "#teso_medio_recaudo_id" ).val().split('-');
        var texto_medio_recaudo = [ medio_recaudo[0], $( "#teso_medio_recaudo_id option:selected" ).text() ];
        
        if ( medio_recaudo[1] == 'Tarjeta bancaria')
        {
            var texto_caja = [0,''];
            var texto_cuenta_bancaria = [
                $('#teso_cuenta_bancaria_id').val(),
                $('#teso_cuenta_bancaria_id option:selected').text()
            ];
        }else{
            var texto_cuenta_bancaria = [0,''];
            var texto_caja = [
                $('#teso_caja_id').val(),
                $('#teso_caja_id option:selected').text()
            ];
        }

        var texto_motivo = [ $( "#teso_motivo_id" ).val(), $( "#teso_motivo_id option:selected" ).text() ];


        var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_linea_medio_recaudo'><i class='fa fa-btn fa-trash'></i></button>";


        celda_valor_total = '<td class="valor_total">$'+valor_total+'</td>';

        $('#ingreso_registros_medios_recaudo').find('tbody:last').append('<tr>'+
            '<td><span style="color:white;">'+texto_medio_recaudo[0]+'-</span><span>'+texto_medio_recaudo[1]+'</span></td>'+
            '<td><span style="color:white;">'+texto_motivo[0]+'-</span><span>'+texto_motivo[1]+'</span></td>'+
            '<td><span style="color:white;">'+texto_caja[0]+'-</span><span>'+texto_caja[1]+'</span></td>'+
            '<td><span style="color:white;">'+texto_cuenta_bancaria[0]+'-</span><span>'+texto_cuenta_bancaria[1]+'</span></td>'+
            celda_valor_total+
            '<td>'+btn_borrar+'</td>'+
            '</tr>');

        // Se calculan los totales para la última fila
        calcular_totales_medio_recaudos();
        reset_form_registro();

        // deshabilitar_campos_form_create();
        $('#btn_guardar').show();

    }else{

        $('#valor_total').attr('style','background-color:#FF8C8C;');
        $('#valor_total').focus();

        alert('Datos incorrectos o incompletos. Por favor verifique.');

        if ($('#total_valor_total').text()=='$0.00') {
            $('#btn_continuar2').hide();
        }
    }
});

/*
    ** Al eliminar una fila
    */
// Se utiliza otra forma con $(document) porque el $('#btn_eliminar_linea_medio_recaudo') no funciona pues
// es un elemento agregadi despues de que se cargó la página
$(document).on('click', '.btn_eliminar_linea_medio_recaudo', function(event) {
    event.preventDefault();
    var fila = $(this).closest("tr");
    fila.remove();
    calcular_totales_medio_recaudos();
    if ($('#total_valor_total').text()=='$0.00')
    {
        $('#efectivo_recibido').removeAttr( 'readonly' );
    }
});

function calcular_totales_medio_recaudos()
{
    var sum = 0.0;
    sum = 0.0;
    $('.valor_total').each(function()
    {
        var cadena = $(this).text();
        sum += parseFloat(cadena.substring(1));
    });

    $('#total_valor_total').text("$"+sum.toFixed(2));
    $('#suma_cambio').val(sum);
    $('#total_valor_total').actualizar_medio_recaudo();
}

function habilitar_text($control){
    $control.removeAttr('disabled');
    $control.attr('style','background-color:white;');
}

function deshabilitar_text($control){
    $control.attr('style','background-color:#ECECE5;');
    $control.attr('disabled','disabled');
}

function reset_form_registro(){

    /*var url = '../../tesoreria/ajax_get_motivos/'+$('#teso_tipo_motivo').val();
    $.get( url, function( datos ) {
        $('#teso_motivo_id').html(datos);
    });
    */

     $('#form_registro input[type="text"]').val('');

     $('#teso_medio_recaudo_id').val('');
     $('#teso_cuenta_bancaria_id').val('');
     $('#teso_caja_id').val('');
     $('#valor_total').val('');


    // $('#form_registro input[type="text"]').attr('style','background-color:#ECECE5;');
    // $('#form_registro input[type="text"]').attr('disabled','disabled');

    $('#div_caja').hide();
    $('#div_cuenta_bancaria').hide();

    $('#btn_agregar').hide();

    $('#teso_medio_recaudo_id').val('');
    $('#teso_medio_recaudo_id').focus();
}

function habilitar_campos_form_create()
{
    $('#fecha').removeAttr('disabled');
    $('.custom-combobox').show();

    // Se revierte el cambio de name del select core_tercero_id
    $('#id_tercero').attr('name','id_tercero');

    $('#core_tercero_id').hide();
    $('#core_tercero_id').removeAttr('disabled');
    $('#core_tercero_id').attr('name','core_tercero_id');

    $('#teso_tipo_motivo').removeAttr('disabled');
}