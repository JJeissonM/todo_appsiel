$(document).ready(function () {

    
    $("form").submit(function(e){
        e.preventDefault();
    });

    $('#bs_boton_guardar').click(function(event){

        event.preventDefault();

        var inv_grupo_id = $("#inv_grupo_id").val();
        var prefijo_referencia_id = $("#prefijo_referencia_id").val();
        var tipo_prenda_id = $("#tipo_prenda_id").val();
        var paleta_color_id = $("#paleta_color_id").val();
        var tipo_material_id = $("#tipo_material_id").val();
        
        var registro_id = 0;

        if ( $("#datos_registro").val() !== undefined ) {
            var datos_registro = jQuery.parseJSON( $("#datos_registro").val() );
            registro_id = datos_registro.id;
        }

        var url = url_raiz + '/inv_item_mandatario_validar_prenda_unica/' + registro_id + '/' + inv_grupo_id + '/' + prefijo_referencia_id + '/' + tipo_prenda_id + '/' + paleta_color_id + '/' + tipo_material_id;

        $.get(url, function(data) {
            if (data.status == 'error') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Advertencia!',
                    text: data.message,
                });
            } else {
                // Si no existe, proceder a guardar
                $("form").unbind('submit').submit();
            }
        });

    });

});