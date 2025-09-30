var tipo_material_id, label_tipo_material;
var tipo_material = null;
$(document).ready(function () {

    $("#bs_boton_guardar").after('<a href="#" class="btn btn-primary btn-xs" id="btn_enviar_form">Guardar</a>');

    $("#bs_boton_guardar").hide();

    $('#btn_enviar_form').click(function(event){

        event.preventDefault();

        var inv_grupo_id = $("#inv_grupo_id").val();
        var prefijo_referencia_id = $("#prefijo_referencia_id").val();
        var tipo_prenda_id = $("#tipo_prenda_id").val();
        var paleta_color_id = $("#paleta_color_id").val();
        var tipo_material_id = $("#tipo_material_id").val();
        
        if ( $("#descripcion").val() == '' || inv_grupo_id == '' || prefijo_referencia_id == '' || tipo_prenda_id == '' || paleta_color_id == '' || tipo_material_id == '') {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Debe completar todos los campos obligatorios.',
            });
            return false;
        }
        
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
                $("form").submit();
            }
        });

    });

    $('#descripcion_detalle').keyup(function(event){
        
        var tipo_prenda_id = $("#tipo_prenda_id").val();
        
        if ( tipo_prenda_id == '' ) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Ingrese el Tipo de prenda.',
            });
            return false;
        }
        
        var tipo_material_id = $("#tipo_material_id").val();

        if ( tipo_material_id == '' ) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Ingrese el Material.',
            });
            return false;
        }

        set_descripcion();
    });

    $('#tipo_material_id').change(function(event){
        tipo_material = null;
        set_descripcion();
    });

    $('#tipo_prenda_id').change(function(event){
        set_descripcion();
    });

    /**
     * 
     */
    function set_descripcion()
    {
        if ( tipo_material === null ) {

            $('#div_cargando').show();
            $.get( url_raiz + '/inv_get_tipo_material/' + $("#tipo_material_id").val(), function(data) {
            
                $('#div_cargando').hide();
                tipo_material = data;

                label_tipo_material = '';
                if (tipo_material.mostrar_en_descripcion_de_prenda) {
                    label_tipo_material = ' ' + tipo_material.descripcion;
                }

                set_label_tipo_material();
            });
        }else{
            set_label_tipo_material();
        }
    }

    function set_label_tipo_material()
    {
        var descripcion = $("#tipo_prenda_id option:selected").text() + ' ' + $('#descripcion_detalle').val() + label_tipo_material;

        $("#lbl_descripcion").text( descripcion );
        $("#descripcion").val( descripcion );
    }

});