function ventana_imprimir_fe(url) {
	ventana_factura = window.open('', "Impresión de Factura Electronica", "width=400,height=600,menubar=no");

	ventana_factura.document.write( '<h3 style="padding: 45px;">Cargando . . . .</h3>' );

    ventana_factura.location = url;
}

function validar_datos_tercero()
{
    var status = 'success';
    var message = '';

    if ( $('#numero_identificacion').val() == '' || $('#numero_identificacion').val().length < 7 )
    {
        status = 'error';
        message += ' - Revisar NIT/CC. No puede estar vacio. Debe tener mas de 6 caracteres.';
    }

    return {
        'status': status,
        'message': message
    };
}

$(document).ready(function () {

    // GUARDAR EL FORMULARIO
    $('#btn_guardar_factura_electronica').click(function (event){
        event.preventDefault();

        if( hay_productos == 0 )
        {
            Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: 'No ha ingresado productos.'
            });
            reset_linea_ingreso_default();
            reset_efectivo_recibido();
            $('#btn_nuevo').hide();
            return false;
        }

        var val_tercero = validar_datos_tercero();
        if ( val_tercero.status == 'error' ) {
            Swal.fire({
                icon: 'error',
                title: 'Alerta!',
                text: val_tercero.message
            });
            return false;
        }

        if ( !validar_producto_con_contorno() ) {
          Swal.fire({
              icon: 'warning',
              title: 'Advertencia!',
              text: 'Has ingresado productos que necesitan Contorno, pero NO está agregado el Contorno.'
          });
          
          return false;
        }

        if( $('#manejar_propinas').val() == 1 )
        {
            if( $('#valor_propina').val() != 0 )
            {
                if ( !permitir_guardar_factura_con_propina() ) 
                {
                    return false;    
                }
            }
        }

        if( $('#manejar_datafono').val() == 1 )
        {
            if( $('#valor_datafono').val() != 0 )
            {
                if ( !permitir_guardar_factura_con_datafono() ) 
                {
                    return false;    
                }
            }
        }

        // Desactivar el click del botón
        $( this ).html( '<i class="fa fa-spinner fa-spin"></i> Guardando' );
        $( this ).attr( 'disabled', 'disabled' );
        $( this ).attr( 'id', 'btn_guardando_fe' );

        $('#linea_ingreso_default').remove();

        var table = $('#ingreso_registros').tableToJSON();        

        json_table2 = get_json_registros_medios_recaudo();

        if( $('#manejar_propinas').val() == 1 )
        {
            // Si hay propina, siempre va a venir una sola linea de medio de pago
            json_table2 = separar_json_linea_medios_recaudo( json_table2 );
        }

        if( $('#manejar_datafono').val() == 1 )
        {
            // Si hay Comision por datafono, siempre va a venir una sola linea de medio de pago
            json_table2 = separar_json_linea_medios_recaudo( json_table2 );
        }

        // Se asigna el objeto JSON a un campo oculto del formulario
        $('#lineas_registros').val( JSON.stringify( table ) );
        $('#lineas_registros_medios_recaudos').val( json_table2 );

        // Nota: No se puede enviar controles disabled

        var url = $("#form_create").attr('action');
        var data = $("#form_create").serialize();
        
        if( $('#manejar_propinas').val() == 1 )
        {
            data += '&valor_propina=' + $('#valor_propina').val();
        }
        
        if( $('#manejar_datafono').val() == 1 )
        {
            data += '&valor_datafono=' + $('#valor_datafono').val();
        }
        
        $.post(url.replace('pos_factura', 'pos_factura_electronica'), data, function (url_print) {
            $('#btn_guardando_fe').html( '<i class="fa fa-check"></i> Guardar como F.E.' );
            $('#btn_guardando_fe').attr( 'id', 'btn_guardar_factura_electronica' );

            $("#pedido_id").val(0);
            $("#object_anticipos").val('null');
            
            ventana_imprimir_fe( url_print );
            resetear_ventana();

            enfocar_tab_totales();

            if ( $('#action').val() != 'create' )
            {
                location.href = url_raiz + '/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=' + $('#pdv_id').val() + '&action=create';
            }            
        });
        
    });
});