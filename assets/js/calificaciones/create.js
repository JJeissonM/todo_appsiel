$(document).ready(function(){

	$("#curso_id").focus();

    $("#id_asignatura").on('change',function(){
        $('#div_form_ingreso').html( '' );
        $("#id_periodo").focus();
    });

    $("#id_periodo").on('change',function(){
        $('#div_form_ingreso').html( '' );
        $("#btn_continuar").focus();
    });

    $("#btn_continuar").on('click',function(event){
        event.preventDefault();

        if ( !validar_requeridos() )
        {
            return false;
        }			
        
        $('#div_cargando').show();
        $('#div_spin').show();

        var form_consulta = $('#form_filtros');
        var url = form_consulta.attr('action');
        var datos = form_consulta.serialize();
        
        // Enviar formulario de ingreso de productos vía POST
        $.post(url,datos,function(respuesta){
            $('#div_cargando').hide();
            $('#div_spin').hide();

            $('#div_form_ingreso').html( respuesta );

        })
    });

});