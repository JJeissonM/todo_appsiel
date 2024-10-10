
function enviar_impresion( doc_encabezado )
{
    // Imprimir siempre en cocina
    if ($("#usar_complemento_JSPrintManager").val() == 1) {        
        print_comanda(doc_encabezado);        
    }

    // Preguntar para imprimir en cocina (alert)
    if ($("#usar_complemento_JSPrintManager").val() == 2) {
        if (confirm("¿Quiere imprimir COMANDA en la cocina?") == true) {
            print_comanda(doc_encabezado);
        }
    }

    switch ( $("#imprimir_factura_automaticamente").val() ) {
        case '0':
            $("#msj_ventana_impresion_abierta").show();
            ventana_imprimir();
            break;
    
        case '1':
            print_factura(doc_encabezado);
            break;
        case '2':
            if (confirm("¿Quiere enviar la FACTURA a la impresora principal?") == true) {
                print_factura(doc_encabezado);
            }
            break;
        default:
            break;
    }

    resetear_ventana();

    if ($("#action").val() != "create") {
        location.href =
            url_raiz +
            "/pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=" +
            $("#pdv_id").val() +
            "&action=create";
    }
}

//Do printing...
function print_comanda( doc_encabezado ) 
{
    var url = url_raiz + '/sys_send_printing_to_server'

    var data = crear_string_json_para_envio_servidor_impresion_comanda( doc_encabezado )
    
    data.printer_ip = $('#impresora_cocina_por_defecto').val()
    data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

    $('#popup_alerta_success').show();
    $('#popup_alerta_success').css('background-color', 'black');
    $('#popup_alerta_success').text('Enviando impresión de COMANDA a la cocina... Por favor, espere!');

    $.ajax({
        url: url,
        data: data,
        type: 'GET',
        success: function( response, status, jqXHR ) {
            $('#popup_alerta_success').hide();

            var additional_message = 'Impresión enviada.';
            var icon = 'info'
            var title = 'Muy bien!'
            if ( response.status == 'Pendiente' )
            {
                additional_message = '... ... ... Sin embargo la impresión NO fué enviada. Debe Re-imprimir la factura.';
                icon = 'warning'
                title = 'Guardado con Novedades!'
            }

            Swal.fire({
                icon: icon,
                title: title,
                text: 'Factura ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creada correctamente. ' + additional_message
            }); 
        },
        error: function( response, status, jqXHR ) { 
            $('#popup_alerta_success').hide();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'No se pudo enviar la impresión a la cocina!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
            });
        }
    });
}

function crear_string_json_para_envio_servidor_impresion_comanda( doc_encabezado )
{
    var json = {
        'header': {
                    'transaction_label': doc_encabezado.doc_encabezado_documento_transaccion_descripcion,
                    'date': doc_encabezado.doc_encabezado_fecha,
                    'customer_name': doc_encabezado.doc_encabezado_tercero_nombre_completo,
                    'number_label': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo,
                    'seller_label': doc_encabezado.doc_encabezado_vendedor_descripcion,
                    items_quantity: doc_encabezado.cantidad_total_productos,
                    'detail': doc_encabezado.doc_encabezado_descripcion
                }
            }
    
    var lines = {}
    var i = 0;
    $('.linea_registro').each(function(){
        
        lines[i] = {
                    'item': $(this).find('.lbl_producto_descripcion').text(),
                    'quantity': $(this).find('.cantidad').text()
                }
        i++
    });
    
    json.lines = lines

    return json
}


function print_factura( doc_encabezado ) 
{
    var url = url_raiz + '/sys_send_printing_to_server'

    var data = crear_string_json_para_envio_servidor_impresion_factura( doc_encabezado )
    
    data.printer_ip = $('#impresora_principal_por_defecto').val()
    data.url_servidor_impresion = $('#url_post_servidor_impresion').val()

    $('#popup_alerta_success').show();
    $('#popup_alerta_success').css('background-color', 'black');
    $('#popup_alerta_success').text('Enviando impresión a la impresora PRINCIPAL... Por favor, espere!');

    $.ajax({
        url: url,
        data: data,
        type: 'GET',
        success: function( response, status, jqXHR ) {
            $('#popup_alerta_success').hide();

            var additional_message = 'Impresión enviada.';
            var icon = 'info'
            var title = 'Muy bien!'
            if ( response.status == 'Pendiente' )
            {
                additional_message = '... ... ... Sin embargo la impresión NO fué enviada. Debe Re-imprimir la factura.';
                icon = 'warning'
                title = 'Guardado con Novedades!'
            }

            Swal.fire({
                icon: icon,
                title: title,
                text: 'Factura ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creada correctamente. ' + additional_message
            }); 
        },
        error: function( response, status, jqXHR ) { 
            $('#popup_alerta_success').hide();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'No se pudo enviar la impresión a la impresora PRINCIPAL!' + "\n" + JSON.stringify(response)  + "\n" +  status  + "\n" +  JSON.stringify(jqXHR)
            });
        }
    });
}

function crear_string_json_para_envio_servidor_impresion_factura( doc_encabezado )
{    
    var json = {
        'header': {
                    'transaction_label': doc_encabezado.doc_encabezado_documento_transaccion_descripcion,
                    'date': doc_encabezado.doc_encabezado_fecha,
                    'customer_name': doc_encabezado.doc_encabezado_tercero_nombre_completo,
                    'number_label': doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo,
                    'seller_label': doc_encabezado.doc_encabezado_vendedor_descripcion,
                    items_quantity: doc_encabezado.cantidad_total_productos,
                    'detail': doc_encabezado.doc_encabezado_descripcion,
                    'empresa': doc_encabezado.empresa,
                    'etiquetas': doc_encabezado.etiquetas,
                    'resolucion': doc_encabezado.resolucion
                }
            }
    
    var lines = {}
    var i = 0;
    $('.linea_registro').each(function(){
        
        lines[i] = {
                    'item': $(this).find('.lbl_producto_descripcion').text(),
                    'quantity': $(this).find('.cantidad').text()
                }
        i++
    });
    
    json.lines = lines

    return json
}