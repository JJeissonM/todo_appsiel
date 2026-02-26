var hay_productos = 0;
var url_raiz, redondear_centena, numero_linea;
var productos, precios, descuentos, clientes, cliente_default, forma_pago_default, fecha_vencimiento_default;
var productos_index_por_id = {};
var productos_index_por_codigo_barras = {};

$('#btn_nuevo').hide();
$('#btnPaula').hide();

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

function construir_indices_productos()
{
    productos_index_por_id = {};
    productos_index_por_codigo_barras = {};

    if (!Array.isArray(productos)) {
        return;
    }

    productos.forEach(function (producto) {
        var product_id = parseInt(producto.id);
        if (!isNaN(product_id)) {
            productos_index_por_id[product_id] = producto;
        }

        if (producto.codigo_barras) {
            productos_index_por_codigo_barras[String(producto.codigo_barras)] = producto;
        }
    });
}

function get_producto_por_id(item_id)
{
    var id = parseInt(item_id);
    if (isNaN(id)) {
        return undefined;
    }

    if (!productos_index_por_id[id] && Array.isArray(productos) && productos.length) {
        construir_indices_productos();
    }

    if (productos_index_por_id[id]) {
        return productos_index_por_id[id];
    }

    if (!Array.isArray(productos)) {
        return undefined;
    }

    return productos.find(function (item) {
        return item.id === id || parseInt(item.id) === id;
    });
}

function get_producto_desde_busqueda(valor_busqueda)
{
    var valor_limpio = String(valor_busqueda || '').trim();
    if (valor_limpio === '') {
        return { producto: undefined, campo_busqueda: '' };
    }

    if (valor_limpio.length > 5) {
        if (!productos_index_por_codigo_barras[valor_limpio] && Array.isArray(productos) && productos.length) {
            construir_indices_productos();
        }

        var producto_por_codigo = productos_index_por_codigo_barras[valor_limpio];
        if (!producto_por_codigo && Array.isArray(productos)) {
            producto_por_codigo = productos.find(function (item) {
                return String(item.codigo_barras || '') === valor_limpio;
            });
        }

        return {
            producto: producto_por_codigo,
            campo_busqueda: 'codigo_barras'
        };
    }

    return {
        producto: get_producto_por_id(valor_limpio),
        campo_busqueda: 'id'
    };
}

function cargar_producto_en_linea(producto)
{
    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;
    costo_unitario = producto.costo_promedio;

    $('#inv_producto_id').val(producto.descripcion);
    $('#precio_unitario').val(get_precio(producto.id));
    $('#tasa_descuento').val(get_descuento(producto.id));
}

function preparar_lineas_registros_para_envio()
{
    $('#linea_ingreso_default').remove();

    var table = $('#ingreso_registros').tableToJSON();
    $('#lineas_registros').val(JSON.stringify(table));

    // No se puede enviar controles disabled
    $('#cliente_input').removeAttr('disabled');
    $('#fecha').removeAttr('disabled');
    $('#inv_bodega_id').removeAttr('disabled');
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
    $('#doc_encabezado_hora_creacion').text( doc_encabezado.doc_encabezado_hora_creacion);
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
    set_cantidades_ingresadas();
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

function activar_boton_guardar_factura() {

	$('#btn_guardar_factura').attr('disabled', 'disabled');

	if (total_cambio.toFixed(0) >= 0)
		$('#btn_guardar_factura').removeAttr('disabled');

};

function mandar_codigo2(item_id, numero_linea_param) {
	
	if ( $('#lbl_vendedor_mesero').text() == '') {
		alert('Debe seleccionar un MESERO.');
		return false;
	}

	if ( $('#lbl_mesa_seleccionada').text() == '') {
		alert('Debe seleccionar una MESA.');
		return false;
	}
		
	var producto = get_producto_por_id(item_id);
    if (producto === undefined) {
        $('#popup_alerta').show();
        $('#popup_alerta').css('background-color', 'red');
        $('#popup_alerta').text('Producto no encontrado.');
        return false;
    }

	cargar_producto_en_linea(producto);
	cantidad = 1;
	$('#cantidad').val(cantidad);
	calcular_valor_descuento();
	calcular_impuestos();
	if ( !calcular_precio_total(true) )
	{
		return false;
	}
	numero_linea = 1;

	if ($('#manejar_platillos_con_contorno').val() == 0) {
		//$("#btn_"+item_id).hide();
	}
    
    $('#btn_guardar_factura').removeAttr('disabled');
    
	agregar_la_linea2();
}



/**
 * 
 * @param {*} item_id 
 * @param {*} name_grupo_id 
 * @returns 
 */
function item_is_in_group( item_id, name_grupo_id )
{
    var producto = get_producto_por_id(item_id);
    if (typeof producto === 'undefined') {
        return false;
    }

    var arr_grupos = $('#' + name_grupo_id).val().split(',').map(Number);
	
    if ( arr_grupos.includes( producto.inv_grupo_id ) ) {
      return true;
    }

    return false;
}

$(document).ready(function () {
    construir_indices_productos();

        var apm_modal_instance = null;
    var apm_first_check_deadline = Date.now() + 5000;

    function is_apm_mode()
    {
        return ($('#metodo_impresion_pedido_restaurante').val() || 'normal') === 'apm';
    }

    function show_apm_modal()
    {
        if ( apm_modal_instance ) {
            return;
        }
        apm_modal_instance = Swal.fire({
            icon: 'error',
            title: 'APM no conectado',
            text: 'No se puede tomar pedidos porque Appsiel Print Manager (APM) no esta conectado.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
    }

    function close_apm_modal()
    {
        if ( apm_modal_instance ) {
            Swal.close();
            apm_modal_instance = null;
        }
    }

    function set_apm_blocked_state( blocked, silent )
    {
        if ( blocked ) {
            $('#btn_guardar_factura').attr('disabled', 'disabled');
            $('#inv_producto_id').attr('disabled', 'disabled');
            $('#cantidad').attr('disabled', 'disabled');
            $('#precio_unitario').attr('disabled', 'disabled');
            $('#tasa_descuento').attr('disabled', 'disabled');
            $('#tasa_impuesto').attr('disabled', 'disabled');
            $('#precio_total').attr('disabled', 'disabled');
            $('#accordionExample').find('button').attr('disabled', 'disabled');
            if ( !silent ) {
                show_apm_modal();
            }
        } else {
            $('#btn_guardar_factura').removeAttr('disabled');
            $('#inv_producto_id').removeAttr('disabled');
            $('#cantidad').removeAttr('disabled');
            $('#precio_unitario').removeAttr('disabled');
            $('#tasa_descuento').removeAttr('disabled');
            $('#tasa_impuesto').removeAttr('disabled');
            $('#precio_total').removeAttr('disabled');
            $('#accordionExample').find('button').removeAttr('disabled');
            close_apm_modal();
        }
    }

    function request_apm_connection()
    {
        if ( window.APM_CLIENT && typeof window.APM_CLIENT.connect === 'function' ) {
            window.APM_CLIENT.connect();
        }
    }

    function apm_is_open()
    {
        return !!(window.APM_CLIENT && window.APM_CLIENT.socket && window.APM_CLIENT.socket.readyState === WebSocket.OPEN);
    }

    function asegurar_apm_conectado()
    {
        if ( !is_apm_mode() ) {
            set_apm_blocked_state(false, true);
            return true;
        }

        request_apm_connection();

        if ( apm_is_open() ) {
            set_apm_blocked_state(false, true);
            return true;
        }

        var still_connecting = Date.now() < apm_first_check_deadline || (window.APM_CLIENT && window.APM_CLIENT.socket && window.APM_CLIENT.socket.readyState === WebSocket.CONNECTING);
        set_apm_blocked_state(true, still_connecting);
        return false;
    }

    if ( window.APM_CLIENT && typeof window.APM_CLIENT.setLogger === 'function' ) {
        window.APM_CLIENT.setLogger(function(message, type) {
            if ( !is_apm_mode() ) {
                return;
            }
            if ( type === 'success' ) {
                set_apm_blocked_state(false, true);
            }
            if ( type === 'warning' || type === 'error' ) {
                set_apm_blocked_state(true, false);
            }
        });
    }

    // Validar APM al cargar y luego cada 2 segundos
    request_apm_connection();
    asegurar_apm_conectado();
    setInterval(asegurar_apm_conectado, 2000);

    if ( $('#action').val() != 'create' )
    {
        reset_efectivo_recibido();
        $('#efectivo_recibido').attr( 'readonly', 'readonly');
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
                var resultado_busqueda = get_producto_desde_busqueda($(this).val());
                var producto = resultado_busqueda.producto;
                var campo_busqueda = resultado_busqueda.campo_busqueda;

                if (producto !== undefined) {
                    cargar_producto_en_linea(producto);

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
        set_cantidades_ingresadas();

    });

    //$(document).on('dblclick', '#btn_guardar_factura', function(event) {
    $('#btn_guardar_factura').dblclick(function (event){
        event.preventDefault();
        return false;
    });

        // GUARDAR EL FORMULARIO
    $('#btn_guardar_factura').click(function (event){
        event.preventDefault();
        $('#btn_guardar_factura').find('i').first().attr('class','fa fa-spinner fa-spin');

        $('#btn_guardar_factura').prop('id','btn_guardar_factura_no');

        if( hay_productos == 0 )
        {
            Swal.fire({
                icon: 'warning',
                title: 'G1 Advertencia!',
                text: 'No ha ingresado productos.'
            }).then(function () {
                restablecer_btn_guardar_factura();
            });
            reset_linea_ingreso_default();
            reset_efectivo_recibido();
            $('#btn_nuevo').hide();

            return false;
        }

        if ( !validar_producto_con_contorno() ) {
            Swal.fire({
                icon: 'warning',
                title: 'Advertencia!',
                text: 'Ha ingresado productos que requieren Contorno, pero NO ha agregado el Contorno.'
            }).then(function () {
                restablecer_btn_guardar_factura();
            });

            return false;
        }

        $( this ).attr( 'disabled', 'disabled' );

        preparar_lineas_registros_para_envio();

        var url = $("#form_create").attr('action');
        var data = $("#form_create").serialize() + "&descripcion=" + $('#descripcion').val();

        $.post(url, data , function (doc_encabezado) {// Almacenar el pedido

            enviar_impresion( doc_encabezado )

        }).fail(function (xhr) {
            restablecer_btn_guardar_factura();
            var mensaje = 'No se pudo guardar el pedido. Verifique la conexión e intente nuevamente.';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                mensaje = xhr.responseJSON.message;
            }

            if (xhr && xhr.status === 409 && typeof activar_mesas_disponibles_mesero === 'function') {
                activar_mesas_disponibles_mesero();
            }

            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: mensaje
            });
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

        preparar_lineas_registros_para_envio();
        
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
                    var url = url_raiz + "/" + "vtas_pedidos_restaurante_cancel" + "/" + $('#pedido_id').val() + "/" + email;

                    $.get(url, function (pedido) {

                        reset_datos_pedido();
                        
                        $("#modal_usuario_supervisor").modal("hide");

                        Swal.fire({
                            icon: 'info',
                            title: 'Pedido anulado correctamente!',
                            text: pedido.doc_encabezado_documento_transaccion_prefijo_consecutivo
                        });

                        $('#div_pedidos_mesero_para_una_mesa').html('');

                        
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

