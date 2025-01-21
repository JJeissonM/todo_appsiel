var hay_productos = 0;
var redondear_centena, numero_linea;
var productos, precios, descuentos, clientes, cliente_default, forma_pago_default, fecha_vencimiento_default;

$(document).ready(function () {

    //Al hacer click en alguna de las sugerencias (escoger un producto)
    $(document).on('click', '.cargar_pedido_para_facturar', function () {
        
        var url = $(this).attr('data-href');

        $.get( url )
            .done(function (data) {
                
                seleccionar_cliente_pedido(data.cliente);
                agregar_lineas_pedido(data.lineas_registros);

                hay_productos = data.numero_lineas;
                $('#numero_lineas').text(hay_productos);

				$("#myModal2").modal("hide");

                // LLenar otros campos
                $("#descripcion").val(data.pedido.descripcion);
                $("#pedido_id").val(data.pedido.id);

                $("#btn_cancelar").hide();
                $("#btn_cancelar_pedido").show();

                // Medios de pago
                $("#btn_nuevo").show();
                
                // Se calculan los totales
                calcular_totales();

                $("#efectivo_recibido").focus();
            });

    });

    $(document).on('click', '#btn_cancelar_pedido', function (e) {
        e.preventDefault();

        $("#pedido_id").val(0);

        resetear_ventana2();
    });    

    function resetear_ventana2()
    {
    	$('#tabla_productos_facturados').find('tbody').html('');
    	$('#tabla_productos_facturados2').find('tbody').html('');
    	reset_campos_formulario();
    	reset_tabla_ingreso_items2();
    	reset_resumen_de_totales2();
        reset_linea_ingreso_default2();

        $("#btn_cancelar").show();
        $("#btn_cancelar_pedido").hide();

    }

    function reset_tabla_ingreso_items2()
    {
        $('.linea_registro').each(function () {
            $(this).remove();
        });
        hay_productos = 0;
        numero_lineas = 0;
        $('#numero_lineas').text('0');
    }

    function reset_resumen_de_totales2()
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
    }

    function reset_linea_ingreso_default2()
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

    function seleccionar_cliente_pedido(data) {
		
        $('#clientes_suggestions').show().html(data);
		$('a.list-group-item.active').focus();

        var item_sugerencia = $('a.list-group-item.active');

        // Asignar descripción al TextInput
        $('#cliente_input').val(item_sugerencia.html());
        $('#cliente_input').css('background-color', 'transparent');

        // Asignar Campos ocultos
        $('#cliente_id').val(item_sugerencia.attr('data-cliente_id'));
        $('#zona_id').val(item_sugerencia.attr('data-zona_id'));
        $('#clase_cliente_id').val(item_sugerencia.attr('data-clase_cliente_id'));
        $('#liquida_impuestos').val(item_sugerencia.attr('data-liquida_impuestos'));
        $('#core_tercero_id').val(item_sugerencia.attr('data-core_tercero_id'));
        $('#lista_precios_id').val(item_sugerencia.attr('data-lista_precios_id'));
        $('#lista_descuentos_id').val(item_sugerencia.attr('data-lista_descuentos_id'));

        // Asignar resto de campos
        $('#vendedor_id').val(item_sugerencia.attr('data-vendedor_id'));
        $('#vendedor_id').attr('data-vendedor_descripcion',item_sugerencia.attr('data-vendedor_descripcion'));
        $('.vendedor_activo').attr('class','btn btn-default btn_vendedor');
        $("button[data-vendedor_id='" + item_sugerencia.attr('data-vendedor_id') +"']").attr('class','btn btn-default btn_vendedor vendedor_activo');
        $(document).prop('title', item_sugerencia.attr('data-vendedor_descripcion'));
        
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


        //Hacemos desaparecer el resto de sugerencias
        $('#clientes_suggestions').html('');
        $('#clientes_suggestions').hide();
    }

    function agregar_lineas_pedido(html_content)
    {
        $("#ingreso_registros tbody").remove();
        $("#ingreso_registros").append(html_content);
    }
});