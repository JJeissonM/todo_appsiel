var hay_productos = 0;
var url_raiz, redondear_centena, numero_linea;
var productos, precios, descuentos, clientes, cliente_default, forma_pago_default, fecha_vencimiento_default;

$('#btn_nuevo').hide();
$('#btnPaula').hide();

function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
{
    $('.text_input_sugerencias').select();
}

$(document).ready(function () {

    if ( $('#action').val() != 'create' )
    {
        reset_efectivo_recibido();
        $('#efectivo_recibido').attr( 'readonly', 'readonly');
    }

    if ( $('#action').val() != 'edit' )
    {
        //$('#fecha').val( get_fecha_hoy() );
    }

    //Al hacer click en alguna de las sugerencias (escoger un producto)
    $(document).on('click', '.list-group-item-cliente', function () {
        seleccionar_cliente($(this));
        return false;
    });


    // Al Activar/Inactivar modo de ingreso
    $('#modo_ingreso').on('click', function () {

        if ($(this).val() == "true") {
            $(this).val("false");
            setCookie("modo_ingreso_codigo_de_barra", "false", 365);
        } else {
            $(this).val("true");
            setCookie("modo_ingreso_codigo_de_barra", "true", 365);
        }

        reset_linea_ingreso_default();
    });

    $('[data-toggle="tooltip"]').tooltip();
    var terminar = 0; // Al presionar ESC dos veces, se posiciona en el botón guardar
    // Al ingresar código, descripción o código de barras del producto
    $('#inv_producto_id').on('keyup', function (event) {

        $("[data-toggle='tooltip']").tooltip('hide');
        $('#popup_alerta').hide();

        var codigo_tecla_presionada = event.which || event.keyCode;

        switch (codigo_tecla_presionada) {
            case 27: // 27 = ESC

                $('#efectivo_recibido').select();

                break;

            case 13: // Al presionar Enter


                if ($(this).val() == '') {
                    return false;
                }

                // Si la longitud del codigo ingresado es mayor que 5
                // se supone que es un código de barras
                var campo_busqueda = '';
                if ($(this).val().length > 5) {
                    var producto = productos.find(item => item.codigo_barras === $(this).val());
                    campo_busqueda = 'codigo_barras';
                } else {
                    var producto = productos.find(item => item.id === parseInt($(this).val()));
                    campo_busqueda = 'id';
                }

                if (producto !== undefined) {

                    tasa_impuesto = producto.tasa_impuesto;
                    inv_producto_id = producto.id;
                    unidad_medida = producto.unidad_medida1;
                    costo_unitario = producto.costo_promedio;

                    $(this).val(producto.descripcion);
                    $('#precio_unitario').val(get_precio(producto.id));
                    $('#tasa_descuento').val(get_descuento(producto.id));

                    if (campo_busqueda == 'id') {
                        $('#cantidad').select();
                    } else {
                        // Por código de barras, se agrega la línea con un unidad de producto
                        $('#cantidad').val(1);

                        cantidad = 1;

                        calcular_valor_descuento();
                        calcular_impuestos();
                        calcular_precio_total();
                        agregar_nueva_linea();
                    }
                } else {
                    $('#popup_alerta').show();
                    $('#popup_alerta').css('background-color', 'red');
                    $('#popup_alerta').text('Producto no encontrado.');
                    $(this).select();
                }
                break;
            default :
                break;
        }

    });

    $('#efectivo_recibido').on('keyup', function (event) {

        var codigo_tecla_presionada = event.which || event.keyCode;

        if (codigo_tecla_presionada == 27) 
        {
            $('#inv_producto_id').focus();
            return false;
        }

        if ($('#valor_total_factura').val() <= 0) 
        {
            return false;
        }

        if ( validar_input_numerico($(this)) && $(this).val() > 0)
        {
            switch (codigo_tecla_presionada) 
            {
                case 13: // Al presionar Enter

                    if (total_cambio.toFixed(0) >= 0) 
                    {
                        $('#btn_guardar_factura').focus();
                    } else {
                        return false;
                    }

                    break;

                default :

                    calcular_totales();

                    $('#total_efectivo_recibido').val( $(this).val() );
                    $.fn.set_label_efectivo_recibido( $(this).val() );

                    $.fn.calcular_total_cambio( $(this).val() );

                    $.fn.activar_boton_guardar_factura();

                    $.fn.cambiar_estilo_div_total_cambio();

                    break;
            }

        } else {
            return false;
        }

    });

    function reset_efectivo_recibido()
    {
        $('#efectivo_recibido').val('');
        $('#total_efectivo_recibido').val(0);
        $('#lbl_efectivo_recibido').text('$ 0');
        $('#total_cambio').text('$ 0');
        $('#lbl_ajuste_al_peso').text('$ ');
        total_cambio = 0;
        $('#btn_guardar_factura').attr('disabled', 'disabled');
    }


    /*
    ** Al digitar la cantidad, se valida la existencia actual y se calcula el precio total
    */
    var ir_al_precio_total = 0;
    $('#cantidad').keyup(function (event) {

        var codigo_tecla_presionada = event.which || event.keyCode;

        if (codigo_tecla_presionada == 13 && $(this).val() == '') 
        {
            $('#precio_unitario').select();
            return false;
        }

        if (validar_input_numerico($(this)) && $(this).val() > 0) 
        {
            cantidad = parseFloat($(this).val());

            if (codigo_tecla_presionada == 13) // ENTER
            {
                agregar_nueva_linea();
            }

            if ($(this).val() != '') 
            {
                calcular_valor_descuento();
                calcular_impuestos();
                calcular_precio_total();
            }
        } else {
            return false;
        }
    });

    function validar_venta_menor_costo()
    {
        if ($("#permitir_venta_menor_costo").val() == 0) {
            var ok = true;

            if (base_impuesto_unitario < costo_unitario) {
                $('#popup_alerta').show();
                $('#popup_alerta').css('background-color', 'red');
                $('#popup_alerta').text('El precio está por debajo del costo de venta del producto.' + ' $' + new Intl.NumberFormat("de-DE").format(costo_unitario.toFixed(2)) + ' + IVA');
                ok = false;
            } else {
                $('#popup_alerta').hide();
                ok = true;
            }
        } else {
            $('#popup_alerta').hide();
            ok = true;
        }

        return ok;
    }

    // Al modificar el precio de venta
    $('#precio_unitario').keyup(function (event) {

        var codigo_tecla_presionada = event.which || event.keyCode;

        if (codigo_tecla_presionada == 13 && $('#cantidad').val() == '') {
            $('#cantidad').select();
            return false;
        }

        if (validar_input_numerico($(this))) {
            precio_unitario = parseFloat($(this).val());

            calcular_valor_descuento();

            calcular_impuestos();

            calcular_precio_total();

            if (codigo_tecla_presionada == 13) {
                $('#tasa_descuento').focus();
            }

        } else {

            $(this).focus();
            return false;
        }

    });

    $('#tasa_descuento').keyup(function () {

        if (validar_input_numerico($(this))) {
            tasa_descuento = parseFloat($(this).val());

            var codigo_tecla_presionada = event.which || event.keyCode;
            if (codigo_tecla_presionada == 13) {
                agregar_nueva_linea();
                return true;
            }

            // máximo valor permitido = 100
            if ($(this).val() > 100) {
                $(this).val(100);
            }

            calcular_valor_descuento();
            calcular_impuestos();
            calcular_precio_total();

        } else {

            $(this).focus();
            return false;
        }
    });

    // Valores unitarios
    function calcular_impuestos()
    {
        var precio_venta = precio_unitario - valor_unitario_descuento;

        base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

        valor_impuesto_unitario = precio_venta - base_impuesto_unitario;
    }

    function calcular_valor_descuento()
    {
        // El descuento se calcula cuando el precio tiene el IVA incluido
        valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
        valor_total_descuento = valor_unitario_descuento * cantidad;
    }

    function reset_descuento() {
        $('#tasa_descuento').val(0);
        calcular_valor_descuento();
    }

    function agregar_nueva_linea() 
    {
        if (!calcular_precio_total()) 
        {
            $('#popup_alerta').show();
            $('#popup_alerta').css('background-color', 'red');
            $('#popup_alerta').text('Error en precio total. Por favor verifique');
            return false;
        }

        agregar_la_linea();
    }

    function agregar_la_linea() 
    {
        if ( !validar_venta_menor_costo() )
        { 
            return false;
        }

        $('#popup_alerta').hide();

        // Se escogen los campos de la fila ingresada
        var fila = $('#linea_ingreso_default');

        var string_fila = $.fn.generar_string_celdas( fila );

        if (string_fila == false) 
        {
            $('#popup_alerta').show();
            $('#popup_alerta').css('background-color', 'red');
            $('#popup_alerta').text('Producto no encontrado.');
            return false;
        }

        // agregar nueva fila a la tabla
        $('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="' + numero_linea + '">' + string_fila + '</tr>');

        // Se calculan los totales
        calcular_totales();

        hay_productos++;
        $('#btn_nuevo').show();
        $('#numero_lineas').text(hay_productos);
        deshabilitar_campos_encabezado();
        
        $('#btn_guardar_factura').removeAttr('disabled');

        // Bajar el Scroll hasta el final de la página
        //$("html, body").animate({scrollTop: $(document).height() + "px"});

        reset_linea_ingreso_default();
        reset_efectivo_recibido();

        //$('#total_valor_total').actualizar_medio_recaudo();

        numero_linea++;
        $('#efectivo_recibido').removeAttr('readonly');
    }

    function deshabilitar_campos_encabezado() 
    {
        $('#cliente_input').attr('disabled', 'disabled');
        $('#fecha').attr('disabled', 'disabled');
        $('#inv_bodega_id').attr('disabled', 'disabled');
    }

    function habilitar_campos_encabezado() 
    {
        $('#cliente_input').removeAttr('disabled');
        $('#fecha').removeAttr('disabled');
        $('#inv_bodega_id').removeAttr('disabled');
    }

    /*
    ** Al eliminar una fila
    */
    $(document).on('click', '.btn_eliminar', function (event) {
        event.preventDefault();
        var fila = $(this).closest("tr");

        fila.remove();

        calcular_totales();

        hay_productos--;
        numero_linea--;
        $('#numero_lineas').text(hay_productos);

        //$('#total_valor_total').actualizar_medio_recaudo();
        reset_linea_ingreso_default();

        if ( hay_productos == 0 )
        {
            habilitar_campos_encabezado();
            reset_efectivo_recibido();
            $('#btn_guardar_factura').attr('disabled', 'disabled');
        }

        var inv_producto_id = parseInt(fila.find('.inv_producto_id').text());
        $("#btn_"+inv_producto_id).show();

    });

    $(document).on('click', '.minus', function(event) {
        event.preventDefault();
        var fila = $(this).closest("tr");
        calcular_precio_total_lbl(fila);
        calcular_totales();
    });

    $(document).on('click', '.plus', function(event) {
        event.preventDefault();
        var fila = $(this).closest("tr");
        calcular_precio_total_lbl(fila);
        calcular_totales();
    });

    //$(document).on('dblclick', '#btn_guardar_factura', function(event) {
    $('#btn_guardar_factura').dblclick(function (event){
        event.preventDefault();
        
        console.log('doble click');

        return false;
    });

    // GUARDAR EL FORMULARIO
    $('#btn_guardar_factura').click(function (event){
        event.preventDefault();
        $('#btn_guardar_factura').children('.fa-check').attr('class','fa fa-spinner fa-spin');
        
        $('#btn_guardar_factura').prop('id','btn_guardar_factura_no');

        if( hay_productos == 0 )
        {
            $('#btn_guardar_factura_no').prop('id','btn_guardar_factura');   
            alert('No ha ingresado productos.');
            reset_linea_ingreso_default();
            reset_efectivo_recibido();
            $('#btn_nuevo').hide();
            return false;
        }

        $( this ).attr( 'disabled', 'disabled' );

        $('#linea_ingreso_default').remove();

        var table = $('#ingreso_registros').tableToJSON();               

        // Se asigna el objeto JSON a un campo oculto del formulario
        $('#lineas_registros').val( JSON.stringify( table ) );

        // No se puede enviar controles disabled
        habilitar_campos_encabezado();

        var url = $("#form_create").attr('action');
        var data = $("#form_create").serialize() + "&descripcion=" + $('#descripcion').val();
        
        $.post(url, data , function (doc_encabezado) {

            if ( $('#mostrar_mensaje_impresion_delegada').val() == 1 ) { 
                $('.btn_vendedor').first().focus();
                Swal.fire({
                    icon: 'info',
                    title: 'Muy bien!',
                    text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. RECUERDA: Debes informar al responsable para su impresión.'
                });
            }else{
                
                if ( $('#usar_servidor_de_impresion').val() == 1 ) {

                    var url = $('#url_post_servidor_impresion').val()

                    var data = crear_string_json_para_envio_servidor_impresion( doc_encabezado )
                    
                    data.printer_ip = $('#printer_ip').val()

                    $.ajax({

                        url: url,
                        data: data,
                        type: 'POST',
                        crossDomain: true,
                        dataType: 'jsonp',
                        success: function( response ) { 
                            $('.btn_vendedor').first().focus();
                            Swal.fire({
                                icon: 'info',
                                title: 'Muy bien!',
                                text: 'Pedido ' + doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo + ' creado correctamente. Impresión enviada.'
                            }); 
                        },
                        error: function( response, status, jqXHR ) { 
                            Swal.fire({
                                icon: 'danger',
                                title: 'Error!',
                                text: 'Failed! \n ' + JSON.stringify(response) + '\n ' + status +  '\n ' + jqXHR
                            });
                        }
                    });

                }else{

                    $('.doc_encabezado_documento_transaccion_descripcion').append(doc_encabezado.doc_encabezado_documento_transaccion_descripcion);

                    $('.doc_encabezado_documento_transaccion_prefijo_consecutivo').text(doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo);

                    llenar_tabla_productos_facturados(doc_encabezado);

                    ventana_imprimir();
                }
            }

            reset_componente_meseros();
            reset_componente_mesas();
            reset_pedidos_mesero_para_una_mesa();

            $('#ingreso_registros').find('tbody').html('');
            $('#descripcion').val('');

            $('#btn_guardar_factura_no').children('.fa-spinner').attr('class','fa fa-save');
            $('#btn_guardar_factura_no').prop('id','btn_guardar_factura');
                        
		    $('#btn_guardar_factura').removeAttr('disabled');

            reset_datos_pedido();
            
            resetear_ventana();
        });
        
    });

    function crear_string_json_para_envio_servidor_impresion( doc_encabezado )
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
    
    $('#btn_imprimir_pedido').click(function (event){
        event.preventDefault();

        var url = url_raiz + "/" + "vtas_cargar_datos_editar_pedido" + "/" + $('#pedido_id').val();

        $.get(url, function (un_pedido) {
            
            $('doc_encabezado_documento_transaccion_descripcion').text( un_pedido.doc_encabezado_documento_transaccion_descripcion );

            $('.doc_encabezado_documento_transaccion_prefijo_consecutivo').text( un_pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo );

            llenar_tabla_productos_facturados(un_pedido);

            ventana_imprimir();

            reset_datos_pedido();
            
            resetear_ventana();
        });
        
    });

    
    $('#btn_modificar_pedido').click(function (event){
        event.preventDefault();

        if( hay_productos == 0 )
        {
            alert('No ha ingresado productos.');
            reset_linea_ingreso_default();
            reset_efectivo_recibido();
            $('#btn_nuevo').hide();
            return false;
        }

        // Desactivar el click del botón
        //$( this ).attr( 'disabled', 'disabled' );

        $('#linea_ingreso_default').remove();

        var table = $('#ingreso_registros').tableToJSON();               

        // Se asigna el objeto JSON a un campo oculto del formulario
        $('#lineas_registros').val( JSON.stringify( table ) );

        // No se puede enviar controles disabled
        habilitar_campos_encabezado();
        
        $("#form_create").attr('method','PUT');

        var url = $("#form_create").attr('action') + '/' + $('#pedido_id').val();
        var data = $("#form_create").serialize();

        $.ajax({
            url: url,
            data: data,
            method: "PUT"
          }).done(function(doc_encabezado) {
            $('doc_encabezado_documento_transaccion_descripcion').text(doc_encabezado.doc_encabezado_documento_transaccion_descripcion);

            $('.doc_encabezado_documento_transaccion_prefijo_consecutivo').text(doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo);
            //reset_componente_meseros();

            llenar_tabla_productos_facturados(doc_encabezado);

            $('#ingreso_registros').find('tbody').html('');
            ventana_imprimir();

            reset_datos_pedido();
            
            resetear_ventana();
          });

    });

    
    $('#btn_crear_nuevo_pedido').click(function (event){
        event.preventDefault();
        reset_datos_pedido();
        mostrar_botones_productos();
        $('#descripcion').val('');
    });

    /*
    $('#btn_anular_pedido').click(function (event){
        event.preventDefault();

        if (confirm('Realmente quiere anular el pedido ' + $('#btn_anular_pedido').attr('data-pedido_label') ) ) {
            var url = url_raiz + "/" + "vtas_pedidos_restaurante_cancel" + "/" + $('#pedido_id').val();

            $.get(url, function (pedido) {

                reset_datos_pedido();
                $('#div_pedidos_mesero_para_una_mesa').html('<div class="alert alert-danger"><strong>'+pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo+'!</strong> Pedido anulado correctamente.</div>');
                
            });
        }
        
    });
    */


    $(document).on('click', ".btn_revisar_pedidos_ventas", function (event) {
        event.preventDefault();

        $('#contenido_modal2').html('');
        $('#div_spin2').fadeIn();

        $("#myModal2").modal(
            {keyboard: true}
        );

        $("#myModal2 .modal-title").text('Consulta de ' + $(this).attr('data-lbl_ventana'));

        $("#myModal2 .btn_edit_modal").hide();
        $("#myModal2 .btn_save_modal").hide();

        var url = url_raiz + "/" + "pos_revisar_pedidos_ventas" + "/" + $('#pdv_id').val();

        $.get(url, function (respuesta) {
            $('#div_spin2').hide();
            $('#contenido_modal2').html(respuesta);
        });
    });

    function reset_componente_meseros()
    {
        $('.vendedor_activo').attr('class','btn btn-default btn_vendedor');
        $('#lbl_vendedor_mesero').text( '' );
    }

    function reset_componente_mesas()
    {
		$('.btn_mesa').removeAttr('disabled');
        $('.btn_mesa').attr('class','btn btn-default btn_mesa');
        $('#lbl_mesa_seleccionada').text( '' );
    }

    function reset_pedidos_mesero_para_una_mesa()
    {
        $('#div_pedidos_mesero_para_una_mesa').text( '' );
    }

    function resetear_ventana()
    {
    	$('#tabla_productos_facturados').find('tbody').html('');
    	reset_campos_formulario();
    	reset_tabla_ingreso_items();
    	reset_resumen_de_totales();
        reset_linea_ingreso_default();
    	reset_tabla_ingreso_medios_pago();
    	reset_efectivo_recibido();

        mostrar_botones_productos();
    }

	function reset_datos_mesa()
	{
		$('#div_pedidos_mesero_para_una_mesa').html('');
		$('#lbl_mesa_seleccionada').html('');
		$('.btn_mesa').removeAttr('disabled');
		$('.btn_mesa').attr('class','btn btn-default btn_mesa');

        $('.linea_registro').each(function () {
            $(this).remove();
        });
        
		hay_productos = 0;
        numero_lineas = 0;

        $('#btn_guardar_factura').show();
        $('#btn_modificar_pedido').hide();
        $('#btn_crear_nuevo_pedido').hide();
        $('#btn_anular_pedido').hide();
        $('#btn_imprimir_pedido').hide();

		 // reset totales
		 $('#total_cantidad').text('0');
 
		 // Total factura  (Sumatoria de precio_total)
		 $('#total_factura').text('$ 0');
		 $('#valor_total_factura').val(0);
	}

    $(document).on('click', '#btn_recalcular_totales', function(event) {
        event.preventDefault();
        calcular_totales();

        $('#btn_nuevo').show();
        $('#numero_lineas').text(hay_productos);
        deshabilitar_campos_encabezado();

        reset_linea_ingreso_default();
        reset_efectivo_recibido();

        $('#total_valor_total').actualizar_medio_recaudo();
        $('#lbl_efectivo_recibido').text('$ 0');
        $('#efectivo_recibido').removeAttr('readonly');
    });

    function llenar_tabla_productos_facturados(doc_encabezado)
    {
        var linea_factura;

        $('.linea_registro').each(function( ){
            $("#btn_"+ $(this).find('.inv_producto_id').text() ).show();
            linea_factura = '<tr> <td style="border: solid 1px gray;"> ' + $(this).find('.lbl_producto_descripcion').text() + ' </td> <td style="border: solid 1px gray; text-align:center;"> ' + $(this).find('.cantidad').text() + ' </td> <td style="border: solid 1px gray;"> <br>  </td></tr>';

            $('#tabla_productos_facturados').find('tbody:last').append( linea_factura );
        });

        $('#doc_encabezado_documento_transaccion_descripcion').text( doc_encabezado.doc_encabezado_documento_transaccion_descripcion);
        $('#doc_encabezado_fecha').text( doc_encabezado.doc_encabezado_fecha);
        $('#doc_encabezado_tercero_nombre_completo').text( doc_encabezado.doc_encabezado_tercero_nombre_completo);
        $('#doc_encabezado_documento_transaccion_prefijo_consecutivo').text( doc_encabezado.doc_encabezado_documento_transaccion_prefijo_consecutivo);
        $('#doc_encabezado_vendedor_descripcion').text( doc_encabezado.doc_encabezado_vendedor_descripcion);
        $('#cantidad_total_productos').text( doc_encabezado.cantidad_total_productos);
        $('#doc_encabezado_descripcion').text( doc_encabezado.doc_encabezado_descripcion);
    }


    function get_hora(i)
    {
      if ( i > 12 )
      {
        i -= 12;
      }
      return i;
    }

    function get_horario(i)
    {
      if ( i > 12 )
      {
        return 'pm';
      }
      return 'am';
    }

    function addZero(i)
    {
      if (i < 10)
      {
        i = "0" + i;
      }
      return i;
    }

    function reset_tabla_ingreso_items()
    {
        $('.linea_registro').each(function () {
            $(this).remove();
        });
        hay_productos = 0;
        numero_lineas = 0;
        $('#numero_lineas').text('0');
    }

    function reset_resumen_de_totales()
    {
        // reset totales
        $('#total_cantidad').text('0');

        // Subtotal (Sumatoria de base_impuestos por cantidad)
        $('#subtotal').text('$ 0');

        $('#descuento').text('$ 0');

        // Total impuestos (Sumatoria de valor_impuesto por cantidad)
        $('#total_impuestos').text('$ 0');

        // Total factura  (Sumatoria de precio_total)
        $('#total_factura').text('$ 0');
        $('#valor_total_factura').val(0);

        $('#div_total_cambio').attr('class', 'default');
    }

    function reset_tabla_ingreso_medios_pago()
    {
        $('#ingreso_registros_medios_recaudo').find('tbody').html('');

        // reset totales
        $('#total_valor_total').text('$0.00');
    }

    function reset_linea_ingreso_default()
    {
        $('#inv_producto_id').val('');
        $('#cantidad').val('');
        $('#precio_unitario').val('');
        $('#tasa_descuento').val('');
        $('#tasa_impuesto').val('');
        $('#precio_total').val('');

        $('#inv_producto_id').focus();

        $('#popup_alerta').hide();

        producto_id = 0;
        precio_total = 0;
        costo_total = 0;
        base_impuesto_total = 0;
        valor_impuesto_total = 0;
        tasa_impuesto = 0;
        tasa_descuento = 0;
        valor_total_descuento = 0;
        cantidad = 0;
        costo_unitario = 0;
        precio_unitario = 0;
        base_impuesto_unitario = 0;
        valor_impuesto_unitario = 0;
        valor_unitario_descuento = 0;
    }

    function calcular_precio_total()
    {
        precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

        $('#precio_total').val(0);

        if ($.isNumeric(precio_total) && precio_total >= 0) 
        {
            $('#precio_total').val(precio_total);
            return true;
        } else {
            precio_total = 0;
            return false;
        }
    }

    function calcular_totales() 
    {
        var total_cantidad = 0.0;
        var subtotal = 0.0;
        var valor_total_descuento = 0.0;
        var total_impuestos = 0.0;
        total_factura = 0.0;

        $('.linea_registro').each(function() {
            var cantidad_linea = parseFloat( $(this).find('.quantity').val() );
            
            total_cantidad += cantidad_linea;
            //precio_unitario = parseFloat( fila.find('.elemento_modificar').eq(1).text() );
            //cantidad += parseFloat( $(this).find('.cantidad').text() );
            subtotal += parseFloat( $(this).find('.base_impuesto').text() ) * cantidad_linea;
            valor_total_descuento += parseFloat( $(this).find('.valor_total_descuento').text() );
            total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() ) * cantidad_linea;
            total_factura += parseFloat( $(this).find('.precio_total').text() );

        });

        $('#total_cantidad').text( new Intl.NumberFormat("de-DE").format(total_cantidad));

        // Subtotal (Sumatoria de base_impuestos por cantidad)
        //var valor = ;
        $('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( (subtotal + valor_total_descuento).toFixed(2) ) );

        $('#descuento').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2)));

        // Total impuestos (Sumatoria de valor_impuesto por cantidad)
        $('#total_impuestos').text('$ ' + new Intl.NumberFormat("de-DE").format(total_impuestos.toFixed(2)));

        // label Total factura  (Sumatoria de precio_total)
        var valor_redondeado = $.fn.redondear_a_centena(total_factura);
        $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_redondeado));

        // input hidden
        $('#valor_total_factura').val(total_factura);

        valor_ajuste_al_peso = valor_redondeado - total_factura;

        $('#lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso));
    }

    $("#btn_listar_items").click(function (event) {

        $("#myModal").modal({keyboard: true});
        $(".btn_edit_modal").hide();
        $(".btn_edit_modal").hide();
        $('#myTable_filter').find('input').css("border", "3px double red");
        $('#myTable_filter').find('input').select();

    });

    var fila;

    function guardar_valor_nuevo(fila) 
    {
        var valor_nuevo = document.getElementById('valor_nuevo').value;

        // Si no cambió el valor_nuevo, no pasa nada
        if (valor_nuevo == valor_actual)
        {
            return false;
        }

        elemento_modificar.html(valor_nuevo);
        elemento_modificar.show();

        $('#inv_producto_id').focus();

        calcular_precio_total_lbl(fila);
        calcular_totales();
        reset_efectivo_recibido();
        $('#total_valor_total').actualizar_medio_recaudo();

        elemento_padre.find('#valor_nuevo').remove();
    }

    function calcular_precio_total_lbl(fila) 
    {
        precio_unitario = parseFloat(fila.find('.precio_unitario').text());
        //base_impuesto_unitario = parseFloat(fila.find('.base_impuesto').text());
        tasa_descuento = parseFloat( fila.find('.tasa_descuento').text() );
        cantidad = parseFloat( fila.find('.quantity').val() );
        //precio_unitario = parseFloat( fila.find('.elemento_modificar').eq(1).text() );

        var tasa_impuesto = parseFloat( fila.find('.lbl_tasa_impuesto').text() );

        valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
        valor_total_descuento = valor_unitario_descuento * cantidad;

        var precio_venta = precio_unitario - valor_unitario_descuento;
        base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

        precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

        fila.find('.cantidad').text(cantidad);

        fila.find('.precio_total').text(precio_total);

        fila.find('.base_impuesto_total').text(base_impuesto_unitario * cantidad);

        fila.find('.valor_total_descuento').text(valor_total_descuento);

        fila.find('.lbl_valor_total_descuento').text(new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2)));

        fila.find('.lbl_precio_total').text(new Intl.NumberFormat("de-DE").format(precio_total.toFixed(2)));
    }

    function setCookie(cname, cvalue, exdays)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    
    $('.btn_mesa').on('click', function (e) {
		e.preventDefault();
        
        if ( $('#lbl_vendedor_mesero').text() == '') {
            alert('Debe seleccionar un MESERO.');
            return false;
        }

		$('.mesa_activa').attr('class','btn btn-default btn_mesa');

		$(this).attr('class','btn btn-default btn_mesa mesa_activa');

        seleccionar_mesa($(this));

	});

    function seleccionar_mesa(item_sugerencia) {

        // Asignar descripción al TextInput
        $('#lbl_mesa_seleccionada').text(item_sugerencia.attr('data-nombre_cliente'));

        // Asignar Campos ocultos
        $('#cliente_id').val(item_sugerencia.attr('data-cliente_id'));
        $('#zona_id').val(item_sugerencia.attr('data-zona_id'));
        $('#clase_cliente_id').val(item_sugerencia.attr('data-clase_cliente_id'));
        $('#liquida_impuestos').val(item_sugerencia.attr('data-liquida_impuestos'));
        $('#core_tercero_id').val(item_sugerencia.attr('data-core_tercero_id'));
        $('#lista_precios_id').val(item_sugerencia.attr('data-lista_precios_id'));
        $('#lista_descuentos_id').val(item_sugerencia.attr('data-lista_descuentos_id'));

        // Asignar resto de campos
        //$('#vendedor_id').val(item_sugerencia.attr('data-vendedor_id'));
        $('#inv_bodega_id').val(item_sugerencia.attr('data-inv_bodega_id'));

        $('#cliente_descripcion').val(item_sugerencia.attr('data-nombre_cliente'));
        $('#cliente_descripcion_aux').val(item_sugerencia.attr('data-nombre_cliente'));
        $('#numero_identificacion').val(item_sugerencia.attr('data-numero_identificacion'));
        $('#direccion1').val(item_sugerencia.attr('data-direccion1'));
        $('#telefono1').val(item_sugerencia.attr('data-telefono1'));

        var forma_pago = 'contado';
        var dias_plazo = parseInt(item_sugerencia.attr('data-dias_plazo'));
        if (dias_plazo > 0) {
            forma_pago = 'credito';
        }
        $('#forma_pago').val(forma_pago);

        // Para llenar la fecha de vencimiento
        var fecha = new Date($('#fecha').val());
        fecha.setDate(fecha.getDate() + (dias_plazo + 1));

        var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
        var dia = fecha.getDate();// + 1; // Se le suma 1,

        if (mes < 10) {
            mes = '0' + mes;
        }

        if (dia < 10) {
            dia = '0' + dia;
        }
        $('#fecha_vencimiento').val(fecha.getFullYear() + '-' + mes + '-' + dia);

        reset_pedidos_cargados_mesa();
        cagar_pedidos_mesero_para_esta_mesa();

    } // FIN seleccionar_mesa

    function reset_pedidos_cargados_mesa()
    {
        $('#div_pedidos_mesero_para_una_mesa').html('');
        
        reset_datos_pedido();
    }

    function reset_datos_pedido()
    {
        $('#ingreso_registros').find('tbody').html('');
        $('#numero_lineas').text('0');
        $('#total_factura').text('$ 0');

        mostrar_botones_productos();

        hay_productos = 0;
        numero_lineas = 0;
        
        $('#btn_guardar_factura').show();
        $('#btn_modificar_pedido').hide();
        $('#btn_crear_nuevo_pedido').hide();
        $('#btn_anular_pedido').hide();
        $('#btn_imprimir_pedido').hide();

        $("#div_ingreso_registros").find('h5').html('Ingreso de productos<br><span style="color:red;">NUEVO PEDIDO</span>');
    }

    function mostrar_botones_productos()
    {
        $('#accordionExample').find('button').each(function () {
            $(this).parent().show();
        });
    }

	function cagar_pedidos_mesero_para_esta_mesa()
	{
        $('#div_pedidos_mesero_para_una_mesa').html('<span class="text-info">Cargando pedidos...</span>');

		var url = url_raiz + "/" + "vtas_get_pedidos_mesero_para_una_mesa" + "/" + $('#vendedor_id').val() + "/" + $('#cliente_id').val();

        $.get(url, function (pedidos) {
            $('#div_pedidos_mesero_para_una_mesa').html('');
			var botones = '';
			pedidos.forEach(un_pedido => {
				botones += '<button class="btn btn-warning btn_pedido_mesero_para_una_mesa"  data-pedido_id="'+un_pedido.pedido_id+'">'+un_pedido.pedido_label+'</button>';
                botones += '&nbsp;&nbsp;&nbsp;&nbsp;';
			});

            if (botones != '' ) {
                $('#div_pedidos_mesero_para_una_mesa').append('<h5><b>'+$('.vendedor_activo').text()+'</b>, tienes estos pedidos pendientes</h5><hr>');                
            }

            $('#div_pedidos_mesero_para_una_mesa').append(botones);

            //llenar_select_mesas_permitidas_para_cambiar();
        });
	}

    function llenar_select_mesas_permitidas_para_cambiar()
    {
        $('#div_cambiar_mesa').show();
        var url = url_raiz + "/" + "vtas_pedidos_restaurante_mesas_permitidas_para_cambiar";
        // + "/" + $('#vendedor_id').val() + "/" + $('#cliente_id').val();

        $.get(url, function (mesas_disponibles) {
            $('#nueva_mesa_id').html('');
			var opciones_select = '<option value="">Seleccionar nueva mesa</option>';
			mesas_disponibles.forEach(mesa => {
				opciones_select += '<option value="'+mesa.mesa_id+'">'+mesa.mesa_descripcion+'</option>';
			});

            $('#nueva_mesa_id').append(opciones_select);
        });
    }

    $(document).on('click', '.btn_pedido_mesero_para_una_mesa', function () {
        
        $("#div_cargando").show();
        $('#ingreso_registros').find('tbody').html('');
        $('#numero_lineas').text('0');

        mostrar_botones_productos();

        var url = url_raiz + "/" + "vtas_cargar_datos_editar_pedido" + "/" + $(this).attr('data-pedido_id');

        $.get(url, function (un_pedido) {
            
            $("#div_cargando").hide();
            
            var lineas_registros = un_pedido.lineas_registro;

            lineas_registros.forEach(linea => {				
                var string_fila = generar_string_celdas_edit( linea );
                $('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="' + linea.numero_linea + '">' + string_fila + '</tr>');
                $("#btn_"+linea.inv_producto_id).hide();
			});

            // Se calculan los totales
            calcular_totales();           
        
            $("#div_ingreso_registros").find('h5').html('Ingreso de productos<br><span style="color:red;">Modificar pedido: '+un_pedido.pedido_label+ ', ' +un_pedido.mesa_label+'</span>');
            $('#pedido_id').val(un_pedido.pedido_id);
            console.log(un_pedido.doc_encabezado_descripcion);
            $('#descripcion').val(un_pedido.doc_encabezado_descripcion);
            
            $('#numero_lineas').text(un_pedido.numero_lineas);

            hay_productos = un_pedido.numero_lineas;
            numero_lineas = un_pedido.numero_lineas;
            
            $('#btn_guardar_factura').hide();
            //$('#btn_modificar_pedido').show();
            $('#btn_anular_pedido').attr('data-pedido_label',un_pedido.pedido_label+ ', ' +un_pedido.mesa_label);
            $('#btn_anular_pedido').show();

            $('#btn_crear_nuevo_pedido').show();
            
            $('#btn_imprimir_pedido').attr('data-pedido_label',un_pedido.pedido_label+ ', ' +un_pedido.mesa_label);
            $('#btn_imprimir_pedido').attr('data-doc_encabezado_documento_transaccion_descripcion',un_pedido.doc_encabezado_documento_transaccion_descripcion);
            $('#btn_imprimir_pedido').attr('data-doc_encabezado_documento_transaccion_prefijo_consecutivo',un_pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo);
            $('#btn_imprimir_pedido').show();
            
        });

    });

    function generar_string_celdas_edit(linea){
    
        var celdas = [];
        var num_celda = 0;
    
        celdas[num_celda] = '<td style="display: none;"><div class="inv_producto_id">' + linea.inv_producto_id + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="precio_unitario">' + linea.precio_unitario + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto">' + linea.base_impuesto_unitario + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="tasa_impuesto">' + linea.tasa_impuesto + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="valor_impuesto">' + linea.valor_impuesto_unitario + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto_total">' + linea.base_impuesto_unitario * linea.cantidad + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="cantidad">' + linea.cantidad + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="precio_total">' + linea.precio_total + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="tasa_descuento">' + linea.tasa_descuento + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td style="display: none;"><div class="valor_total_descuento">' + linea.valor_total_descuento + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td> &nbsp; </td>';
    
        num_celda++;
    
        var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
    
        celdas[num_celda] = '<td> &nbsp;&nbsp; <div class="lbl_producto_descripcion" style="display: inline;"> ' + linea.lbl_producto_descripcion + ' </div> </td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td> ' + linea.cantidad + ' </td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td> <div class="lbl_precio_unitario" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(linea.precio_unitario) + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td>' + linea.tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format(linea.valor_total_descuento.toFixed(0)) + '</div> ) </td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">' + linea.tasa_impuesto + '</div></td>';
    
        num_celda++;
    
        celdas[num_celda] = '<td> <div class="lbl_precio_total" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(linea.precio_total.toFixed(0)) + ' </div> </td> <td> &nbsp; </td>';
    
        var cantidad_celdas = celdas.length;
        var string_celdas = '';
        for (var i = 0; i < cantidad_celdas; i++) {
            string_celdas = string_celdas + celdas[i];
        }

        return string_celdas;
    };

    
    $("#btn_validate_password_supervisor").on('click', function(e){            
		e.preventDefault();
		$(this).children('.fa-check').attr('class','fa fa-spinner fa-spin');
        validate_password_supervisor();
    });

    function validate_password_supervisor()
    {
        var email = 'a3p0';
        var password = 'a3p0';
        if ( $('#email_supervisor').val() != '') {
            email = $('#email_supervisor').val();
        }
        
        if ( $('#password_supervisor').val() != '') {
            password = $('#password_supervisor').val();
        }

        var url = url_raiz + "/" + "core_validate_usuario_supervisor" + "/" + email + "/" + password;

        $.get(url, function (respuesta) {
			
			document.getElementById('btn_validate_password_supervisor').children[0].className = 'fa fa-check';
			
            if (respuesta == 'ok') {                
                if (confirm('Realmente quiere anular el pedido ' + $('#btn_anular_pedido').attr('data-pedido_label') ) ) {
                    var url = url_raiz + "/" + "vtas_pedidos_restaurante_cancel" + "/" + $('#pedido_id').val();

                    $.get(url, function (pedido) {

                        reset_datos_pedido();
                        
                        $("#modal_usuario_supervisor").modal("hide");

                        $('#div_pedidos_mesero_para_una_mesa').html('<div class="alert alert-danger"><strong>'+pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo+'!</strong> Pedido anulado correctamente.</div>');

                        
                    });
                }                
            }else{
                $('#lbl_error_password_supervisor').show();
                hay_error_password = true;
            }
        });
    }

});

$.fn.set_catalogos = function ( pdv_id ) {
    	$('#contenido_modal2').html('Cargando recursos... por favor espere.');

        $("#contenido_modal2").attr('style','text-align: center;color: #42A3DC;');
        $("#myModal2 .modal-content").attr('style','background: transparent;border: 0px solid;box-shadow: none;');
        $('#div_spin2').fadeIn();

        $("#myModal2").modal(
            {backdrop: "static"}
        );

        $("#myModal2 .modal-title").text('');

        $("#myModal2 .btn_edit_modal").hide();
        $("#myModal2 .btn-danger").hide();
        $("#myModal2 .btn_save_modal").hide();
        $("#myModal2 .close").hide();

    	$.get( url_raiz + "/ventas_pos_set_catalogos" + "/" + pdv_id )
            .done(function (datos) {
				
				redondear_centena =  datos.redondear_centena;
				productos =  datos.productos;
				precios =  datos.precios;
				descuentos =  datos.descuentos;
				clientes =  datos.clientes;
				cliente_default =  datos.cliente_default;
				forma_pago_default =  datos.forma_pago_default;
				fecha_vencimiento_default =  datos.fecha_vencimiento_default;

        		$("#myModal2 .modal-content").removeAttr('style');
        		$("#contenido_modal2").removeAttr('style');
        		$("#myModal2 .close").show();
				$("#myModal2").modal("hide");
            });	

};