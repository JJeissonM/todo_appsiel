
function validar_requeridos_form_filtros()
{
    if ($("#curso_id").val() == "" || $("#curso_id").val() == null) {
        $("#curso_id").focus();
        var name_campo = $("#curso_id").attr('name');
        var lbl_campo = $("#curso_id").parent().prev('label').text();
        if( lbl_campo === '' )
        {
            lbl_campo = $("#curso_id").prev('label').text();
        }
        alert( 'Este campo es requerido: ' + lbl_campo + ' (' + name_campo + ')' );

        return false;
    }
    
    if ($("#id_asignatura").val() == "" || $("#id_asignatura").val() == null) {
        $("#id_asignatura").focus();
        var name_campo = $("#id_asignatura").attr('name');
        var lbl_campo = $("#id_asignatura").parent().prev('label').text();
        if( lbl_campo === '' )
        {
            lbl_campo = $("#id_asignatura").prev('label').text();
        }
        alert( 'Este campo es requerido: ' + lbl_campo + ' (' + name_campo + ')' );

        return false;
    }
    
    if ($("#id_periodo").val() == "" || $("#id_periodo").val() == null) {
        $("#id_periodo").focus();
        var name_campo = $("#id_periodo").attr('name');
        var lbl_campo = $("#id_periodo").parent().prev('label').text();
        if( lbl_campo === '' )
        {
            lbl_campo = $("#id_periodo").prev('label').text();
        }
        alert( 'Este campo es requerido: ' + lbl_campo + ' (' + name_campo + ')' );

        return false;
    }

    return true;
}

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

        if ( !validar_requeridos_form_filtros() )
        {
            return false;
        }

        if (typeof window.activarCargaContinuar === 'function') {
            window.activarCargaContinuar();
        }
        
        $('#div_form_ingreso').html('');
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

            if (typeof window.desactivarCargaContinuar === 'function') {
                window.desactivarCargaContinuar();
            }
        }).fail(function(xhr){
            $('#div_cargando').hide();
            $('#div_spin').hide();

            var mensaje = 'No se pudieron cargar las calificaciones. Intente nuevamente.';
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            } else if (xhr.status === 401 || xhr.status === 419) {
                mensaje = 'La sesión ha vencido. Recargue la página e ingrese nuevamente.';
            } else if (xhr.status === 0) {
                mensaje = 'No se pudo conectar con el servidor. Revise su conexión e intente nuevamente.';
            }
            var aviso = $('<div>').addClass('alert alert-danger')
                .attr('role', 'alert').attr('tabindex', '-1').text(mensaje);
            $('#div_form_ingreso').empty().append(aviso);
            aviso.focus();

            if (typeof window.desactivarCargaContinuar === 'function') {
                window.desactivarCargaContinuar();
            }
        });
    });

});
