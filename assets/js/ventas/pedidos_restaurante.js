var hay_productos = 0;
var url_raiz, redondear_centena, numero_linea;
var productos, precios, descuentos, clientes, cliente_default, forma_pago_default, fecha_vencimiento_default;

$('#btn_nuevo').hide();
$('#btnPaula').hide();

function set_catalogos( pdv_id )
{
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

}

function ejecutar_acciones_con_item_sugerencia( item_sugerencia, obj_text_input )
{
    $('.text_input_sugerencias').select();
}

function mostrar_botones_productos()
{
    $('#accordionExample').find('button').each(function () {
        $(this).parent().show();
    });
}


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

                    calcular_totales_quantity();

                    $('#total_efectivo_recibido').val( $(this).val() );
                    set_label_efectivo_recibido( $(this).val() );

                    calcular_total_cambio( $(this).val() );

                    activar_boton_guardar_factura();

                    cambiar_estilo_div_total_cambio();

                    break;
            }

        } else {
            return false;
        }

    });

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

/*
    function reset_descuento() {
        $('#tasa_descuento').val(0);
        calcular_valor_descuento();
    }
*/
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

        var string_fila = generar_string_celdas( fila );

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
        calcular_totales_quantity();

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

        calcular_totales_quantity();

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

    //$(document).on('dblclick', '#btn_guardar_factura', function(event) {
    $('#btn_guardar_factura').dblclick(function (event){
        event.preventDefault();
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
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'No ha ingresado productos.'
            });
            reset_linea_ingreso_default();
            reset_efectivo_recibido();
            $('#btn_nuevo').hide();

            restablecer_btn_guardar_factura()

            return false;
        }

        if ( !validar_producto_con_contorno() ) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Ha ingresado productos que requieren Contorno, pero NO ha agregado el Contorno.'
            });
            
            restablecer_btn_guardar_factura()

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
        
        $.post(url, data , function (doc_encabezado) {// Almacenar el pedido

            enviar_impresion( doc_encabezado )

        });
        
    });
    
    $('#btn_imprimir_pedido').click(function (event){
        event.preventDefault();

        var url = url_raiz + "/" + "vtas_cargar_datos_editar_pedido" + "/" + $('#pedido_id').val();

        $.get(url, function (un_pedido) {
            
            enviar_impresion( un_pedido )

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

    $(document).on('click', '#btn_recalcular_totales', function(event) {
        event.preventDefault();
        calcular_totales_quantity();

        $('#btn_nuevo').show();
        $('#numero_lineas').text(hay_productos);
        deshabilitar_campos_encabezado();

        reset_linea_ingreso_default();
        reset_efectivo_recibido();

        $('#total_valor_total').actualizar_medio_recaudo();
        $('#lbl_efectivo_recibido').text('$ 0');
        $('#efectivo_recibido').removeAttr('readonly');
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

        calcular_precio_total_lbl_quantity(fila);
        calcular_totales_quantity();
        reset_efectivo_recibido();
        $('#total_valor_total').actualizar_medio_recaudo();

        elemento_padre.find('#valor_nuevo').remove();
    }

    function setCookie(cname, cvalue, exdays)
    {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

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
    
    $("#btn_validate_password_supervisor").on('click', function(e){            
		e.preventDefault();
		$(this).children('.fa-check').attr('class','fa fa-spinner fa-spin');
        validate_password_supervisor();
    });

});
