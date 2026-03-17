function ventana_imprimir_fe(url) {
    var ventana_factura = null;

    try {
        ventana_factura = window.open('', "Impresión de Factura Electronica", "width=400,height=600,menubar=no");
    } catch (e) {
        ventana_factura = null;
    }

    // Popup bloqueado por el navegador.
    if (!ventana_factura || ventana_factura.closed || typeof ventana_factura.closed === 'undefined') {
        return false;
    }

    try {
        ventana_factura.document.write( '<h3 style="padding: 45px;">Cargando . . . .</h3>' );
        ventana_factura.location = url;
        return true;
    } catch (e) {
        return false;
    }
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
            if (typeof pos_mostrar_mensaje_validacion_previa === "function") {
                pos_mostrar_mensaje_validacion_previa("sin_productos");
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validacion pendiente',
                    text: 'No hay productos cargados. Agrega al menos un producto antes de continuar.'
                });
            }
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
          if (typeof pos_mostrar_mensaje_validacion_previa === "function") {
              pos_mostrar_mensaje_validacion_previa("contorno_requerido");
          } else {
              Swal.fire({
                  icon: 'warning',
                  title: 'Validacion pendiente',
                  text: 'Hay productos que requieren contorno y aun no fue agregado. Agrega el contorno para continuar.'
              });
          }
          
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

        var url = $("#form_create").attr('action');
        var payload_guardado = null;
        var data = '';
        if (typeof pos_preparar_payload_guardado === "function") {
            payload_guardado = pos_preparar_payload_guardado({ incluir_impuesto_id: false });
            data = payload_guardado.data;
        } else {
            data = $("#form_create").serialize();
        }
        
        $.post(
            url.replace('pos_factura', 'pos_factura_electronica'),
            data, 
            function (response) {
                $('#btn_guardando_fe').html( '<i class="fa fa-check"></i> Guardar como F.E.' );
                $('#btn_guardando_fe').attr( 'id', 'btn_guardar_factura_electronica' );

                var url_print = response;
                var warning_message = '';
                if (response && typeof response === 'object') {
                    if (typeof response.url_print === 'string') {
                        url_print = response.url_print;
                    }
                    if (typeof response.message === 'string' && response.message !== '') {
                        warning_message = response.message;
                    }
                }

                if (typeof url_print !== 'string' || url_print === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La factura se guardó, pero no se obtuvo URL de impresión.'
                    });
                    return false;
                }

                if (typeof pos_reset_contexto_despues_guardado === "function") {
                    pos_reset_contexto_despues_guardado();
                } else {
                    $("#pedido_id").val(0);
                    $("#object_anticipos").val('null');
                    if (typeof update_uniqid === "function") {
                        update_uniqid();
                    } else {
                        $("#uniqid").val( uniqid() );
                    }
                }
                
                var ventana_impresion_abierta = ventana_imprimir_fe( url_print );
                resetear_ventana();

                enfocar_tab_totales();

                if (!ventana_impresion_abierta) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Ventana emergente bloqueada',
                        text: 'La factura se guardo correctamente, pero el navegador bloqueo la ventana de impresion.',
                        confirmButtonText: 'Abrir factura',
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar'
                    }).then(function(result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        var nueva_ventana = window.open(url_print, '_blank');
                        if (!nueva_ventana) {
                            window.location.href = url_print;
                        }
                    });
                }

                if (warning_message !== '') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Advertencia',
                        text: warning_message
                    });
                }

                if ( $('#action').val() != 'create' )
                {
                    location.href = url_raiz + '/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=' + $('#pdv_id').val() + '&action=create';
                }
            }
        ).fail(function (xhr) {
            $('#btn_guardando_fe').html( '<i class="fa fa-check"></i> Guardar como F.E.' );
            $('#btn_guardando_fe').removeAttr('disabled');
            $('#btn_guardando_fe').attr( 'id', 'btn_guardar_factura_electronica' );
            if (typeof pos_mostrar_mensaje_error_guardado === "function") {
                pos_mostrar_mensaje_error_guardado(xhr, {
                    prefijo_titulo: 'FACTURA NO GUARDADA. INTENTA OTRA VEZ!'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No fue posible guardar la Factura Electrónica. Intente nuevamente.'
                });
            }
            return false;
        });
        
    });
});
