function ventana_imprimir_fe(url) {
    try {
        var screenWidth = window.screen && window.screen.availWidth ? window.screen.availWidth : 1024;
        var screenHeight = window.screen && window.screen.availHeight ? window.screen.availHeight : 768;
        var previewWidth = Math.min(420, Math.max(320, Math.floor(screenWidth * 0.42)));
        var previewHeight = Math.min(700, Math.max(500, screenHeight - 100));
        var previewFeatures = [
            "width=" + previewWidth,
            "height=" + previewHeight,
            "left=10",
            "top=60",
            "menubar=no",
            "toolbar=no",
            "location=no",
            "status=no",
            "scrollbars=yes",
            "resizable=yes"
        ].join(",");

        ventana_factura = window.open('', "Impresión de Factura Electronica", previewFeatures);
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
        return ventana_factura;
    } catch (e) {
        return false;
    }
}

function pos_factura_electronica_usa_apm()
{
    return ($('#metodo_impresion_factura_pos').val() || 'normal') === 'apm';
}

function pos_previsualizar_y_preguntar_factura_electronica_apm(urlPrint, docEncabezado, facturaElectronica)
{
    var apmContext = pos_preparar_contexto_factura_electronica_apm(urlPrint, docEncabezado, facturaElectronica);
    return pos_previsualizar_y_preguntar_factura_electronica_apm_preparada(apmContext);
}

function pos_preparar_contexto_factura_electronica_apm(urlPrint, docEncabezado, facturaElectronica)
{
    var payload = crear_payload_apm_factura_electronica(docEncabezado, facturaElectronica);
    var meta = crear_meta_apm_factura_pos(docEncabezado);
    meta.document_type = 'factura_electronica';
    meta.document_label = docEncabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo || meta.document_label;

    return {
        urlPrint: urlPrint,
        docEncabezado: docEncabezado,
        payload: payload,
        meta: meta,
        drawerPrinterId: payload.PrinterId || $('#apm_printer_id_factura_pos').val() || $('#impresora_principal_por_defecto').val() || ''
    };
}

function pos_previsualizar_y_preguntar_factura_electronica_apm_preparada(apmContext)
{
    var payload = apmContext.payload;
    var meta = apmContext.meta;
    var docEncabezado = apmContext.docEncabezado;
    var drawerPrinterId = apmContext.drawerPrinterId;
    var previewWindow = ventana_imprimir_fe(pos_agregar_parametro_url(apmContext.urlPrint, 'no_auto_print', '1'));
    if (!previewWindow) {
        return Promise.resolve(false);
    }

    return pos_esperar_previsualizacion_factura_electronica(previewWindow)
        .then(function () {
            return pos_agregar_confirmacion_apm_en_previsualizacion({
                title: 'Impresion APM',
                question: '¿Desea imprimir la factura electronica por APM? Cerrar esta ventana o presionar Q abre solo el cajon.',
                confirmText: 'Si, imprimir',
                skipText: 'No imprimir'
            });
        })
        .then(function (shouldPrint) {
            if (shouldPrint === null) {
                shouldPrint = false;
            }

            pos_cerrar_previsualizacion_factura();

            if (shouldPrint) {
                return pos_mostrar_modal_apm([
                    {
                        label: 'Factura Electronica',
                        promise: print_factura_apm_preparada(payload, meta)
                    }
                ], docEncabezado).then(function () {
                    return true;
                });
            }

            return pos_mostrar_modal_apm([
                {
                    label: 'OpenDrawer',
                    promise: apm_open_drawer_factura_pos(drawerPrinterId)
                }
            ], docEncabezado).then(function () {
                return true;
            });
        });
}

function pos_agregar_parametro_url(url, key, value)
{
    var separator = String(url).indexOf('?') === -1 ? '?' : '&';
    return url + separator + encodeURIComponent(key) + '=' + encodeURIComponent(value);
}

function pos_esperar_previsualizacion_factura_electronica(previewWindow)
{
    return new Promise(function (resolve) {
        var resolved = false;
        var attempts = 0;
        var maxAttempts = 80;
        var finish = function () {
            if (resolved) {
                return;
            }
            resolved = true;
            setTimeout(resolve, 100);
        };

        var waitForRealDocument = setInterval(function () {
            attempts++;

            try {
                if (!previewWindow || previewWindow.closed) {
                    clearInterval(waitForRealDocument);
                    finish();
                    return;
                }

                var href = String(previewWindow.location && previewWindow.location.href ? previewWindow.location.href : '');
                var readyState = previewWindow.document && previewWindow.document.readyState;
                var bodyText = previewWindow.document && previewWindow.document.body ? previewWindow.document.body.textContent : '';
                var isLoadingPlaceholder = bodyText.indexOf('Cargando') !== -1 && bodyText.length < 80;
                var isRealPreview = href.indexOf('vtas_imprimir') !== -1 || href.indexOf('formato_impresion_id=pos') !== -1;

                if (isRealPreview && readyState === 'complete' && !isLoadingPlaceholder) {
                    clearInterval(waitForRealDocument);
                    finish();
                    return;
                }
            } catch (e) {
                clearInterval(waitForRealDocument);
                finish();
                return;
            }

            if (attempts >= maxAttempts) {
                clearInterval(waitForRealDocument);
                finish();
            }
        }, 250);
    });
}

function pos_mostrar_advertencia_factura_electronica(message)
{
    if (!message) {
        return;
    }

    Swal.fire({
        icon: 'warning',
        title: 'Factura electronica con novedades',
        html: pos_formatear_advertencia_factura_electronica(message),
        confirmButtonText: 'Entendido',
        width: 620
    });
}

function pos_formatear_advertencia_factura_electronica(message)
{
    var cleanText = pos_extraer_texto_advertencia_factura_electronica(message);
    var formatted = pos_escape_html_fe(cleanText)
        .replace(/Regla:/g, '<br><b>Regla:</b>')
        .replace(/Codigo:/g, '<b>Codigo:</b>')
        .replace(/Código:/g, '<b>Codigo:</b>')
        .replace(/Fecha de Respuesta:/g, '<br><b>Fecha de respuesta:</b>')
        .replace(/Mensaje Validación:/g, '<br><b>Mensaje de validacion:</b>');

    return [
        '<div style="text-align:left;max-height:320px;overflow:auto;">',
        '<p>La factura fue guardada, pero el proveedor electronico devolvio novedades de validacion.</p>',
        '<div style="font-size:12px;line-height:1.35;">' + formatted + '</div>',
        '</div>'
    ].join('');
}

function pos_extraer_texto_advertencia_factura_electronica(message)
{
    var container = document.createElement('div');
    container.innerHTML = String(message)
        .replace(/<\/br>/gi, '<br>')
        .replace(/<br\s*\/?>/gi, '\n');

    return (container.textContent || container.innerText || String(message))
        .replace(/\s+\n/g, '\n')
        .replace(/\n\s+/g, '\n')
        .replace(/[ \t]{2,}/g, ' ')
        .trim();
}

function pos_escape_html_fe(value)
{
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/\n/g, '<br>');
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
            if (payload_guardado === null) {
                $('#btn_guardando_fe').html( '<i class="fa fa-check"></i> Guardar como F.E.' );
                $('#btn_guardando_fe').removeAttr( 'disabled' );
                $('#btn_guardando_fe').attr( 'id', 'btn_guardar_factura_electronica' );
                return false;
            }
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

                var doc_encabezado = null;
                var factura_electronica = {};
                if (response && typeof response === 'object') {
                    doc_encabezado = response.doc_encabezado || null;
                    factura_electronica = response.factura_electronica || {};
                    if (doc_encabezado) {
                        doc_encabezado.factura_electronica = factura_electronica;
                    }
                }

                if (pos_factura_electronica_usa_apm() && doc_encabezado) {
                    var apm_context = pos_preparar_contexto_factura_electronica_apm(url_print, doc_encabezado, factura_electronica);

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

                    resetear_ventana();
                    enfocar_tab_totales();

                    pos_previsualizar_y_preguntar_factura_electronica_apm_preparada(apm_context)
                        .then(function (ventana_impresion_abierta) {
                            if (ventana_impresion_abierta === false) {
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
                                setTimeout(function () {
                                    pos_mostrar_advertencia_factura_electronica(warning_message);
                                }, 900);
                            }

                            if ( $('#action').val() != 'create' )
                            {
                                location.href = url_raiz + '/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=' + $('#pdv_id').val() + '&action=create';
                            }
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
                    pos_mostrar_advertencia_factura_electronica(warning_message);
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
