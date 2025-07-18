// Variables de cada línea de ingresos de registros.
var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, tasa_descuento, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id, unidad_medida, total_factura;
var costo_unitario = 0;
var precio_unitario = 0;
var base_impuesto_unitario = 0;
var valor_impuesto_unitario = 0;
var valor_unitario_descuento = 0;
var total_cambio = 0;
var ventana_factura;
var numero_linea = 0;

$('#teso_motivo_id').val(1);
$('#teso_caja_id').val($('#caja_pdv_default_id').val());

// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
// Debe ser complatible con las columnas de la tabla de ingreso de registros
function generar_string_celdas(fila) {
	if (inv_producto_id === undefined) {
		return false;
	}

	var celdas = [];
	var num_celda = 0;

	
	var producto = productos.find((item) => item.id === parseInt( inv_producto_id ));

	var talla = ''
	if ( producto.unidad_medida2 != '') {
		talla = " - " + producto.unidad_medida2
	}

	celdas[num_celda] = '<td style="display: none;"><div class="inv_producto_id">' + inv_producto_id + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="precio_unitario">' + precio_unitario + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto">' + base_impuesto_unitario + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="tasa_impuesto">' + tasa_impuesto + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="valor_impuesto">' + valor_impuesto_unitario + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="base_impuesto_total">' + base_impuesto_unitario * cantidad + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="cantidad">' + cantidad + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="precio_total">' + precio_total + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="tasa_descuento">' + tasa_descuento + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td style="display: none;"><div class="valor_total_descuento">' + valor_total_descuento + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td> &nbsp; </td>';

	num_celda++;

	celdas[num_celda] = '<td> <span style="background-color:#F7B2A3;">' + inv_producto_id + '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' + $('#inv_producto_id').val() + talla + ' </div> </td>';

	num_celda++;

	//celdas[ num_celda ] = '<td>' + cantidad + ' </td>';
	celdas[num_celda] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + cantidad + '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' + unidad_medida + '</div>)' + ' </td>';
	//celdas[num_celda] = '<td> <div class="number-input"><button onclick="this.parentNode.querySelector(\'input[type=number]\').stepDown()" class="minus"></button><input class="cantidad" min="1" name="cantidad" value="' + cantidad + '" type="number" readonly="readonly"><button onclick="this.parentNode.querySelector(\'input[type=number]\').stepUp()" class="plus"></button> </div> </td>';

	num_celda++;

	//celdas[num_celda] = '<td> <div class="lbl_precio_unitario" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(precio_unitario) + '</div></td>';
	celdas[num_celda] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar.">' + precio_unitario + '</div> </div> </td>';

	num_celda++;

	celdas[num_celda] = '<td>' + tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(0)) + '</div> ) </td>';

	num_celda++;

	celdas[num_celda] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">' + tasa_impuesto + '</div></td>';

	num_celda++;

	var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
	celdas[num_celda] = '<td> <div class="lbl_precio_total" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(precio_total.toFixed(0)) + ' </div> </td> <td>' + btn_borrar + '</td>';

	var cantidad_celdas = celdas.length;
	var string_celdas = '';
	for (var i = 0; i < cantidad_celdas; i++) {
		string_celdas = string_celdas + celdas[i];
	}

	inv_producto_id = undefined; // para que no quede en memoria el código del producto

	return string_celdas;
};

function redondear_a_centena(numero, aproximacion_superior = false) {
	if ( redondear_centena == 0 )
	{
		return numero.toFixed(0);
	}

	var millones = 0;
	var millares = 0;
	var centenas = 0;

	var saldo1, saldo2, saldo3;

	if (numero > 999999.99999) {
		// se obtiene solo la parte entera
		millones = Math.trunc(numero / 1000000) * 1000000;
	}

	saldo1 = numero - millones;

	if (saldo1 > 999.99999) {
		// se obtiene solo la parte entera
		millares = Math.trunc(saldo1 / 1000) * 1000;
	}

	saldo2 = saldo1 - millares;

	if (saldo2 > 49.99999) {
		// se obtiene solo la parte entera
		//centenas = Math.trunc( saldo2 / 100 ) * 100;
		centenas = (saldo2 / 100).toFixed(0) * 100;
	}

	return (millones + millares + centenas);

};

function reset_campos_formulario()
{
	//$('#fecha').val( get_fecha_hoy() );
	$('#descripcion').val( '' );

	$('#cliente_id').val( cliente_default.id );
	$('#cliente_input').val( cliente_default.descripcion );
	$('#cliente_input').css('background-color', 'transparent');

	$('#inv_bodega_id').val( cliente_default.inv_bodega_id );
	$('#forma_pago').val( forma_pago_default );
	$('#fecha_vencimiento').val( fecha_vencimiento_default );
	$('#lista_precios_id').val( cliente_default.lista_precios_id );
	$('#lista_descuentos_id').val( cliente_default.lista_descuentos_id );
	$('#liquida_impuestos').val( cliente_default.liquida_impuestos );
	$('#core_tercero_id').val( cliente_default.core_tercero_id );
	$('#zona_id').val( cliente_default.zona_id );
	$('#clase_cliente_id').val( cliente_default.clase_cliente_id );

	$('#cliente_descripcion_aux').val( cliente_default.descripcion );
	$('#numero_identificacion').val( cliente_default.numero_identificacion );
	$('#direccion1').val( cliente_default.direccion1 );
	$('#telefono1').val( cliente_default.telefono1 );

	$('#lineas_registros').val(0); // Input que recoge el listado de productos
}

function get_precio(producto_id)
{
	var precio = precios.find(item => item.producto_codigo === producto_id);

	if (precio === undefined) {
		precio = 0;
	} else {
		precio = precio.precio;
	}


	precio_unitario = precio;

	return precio;
}

var filter_descuento;
function get_descuento(producto_id)
{
	filter_descuento = {
		producto_codigo: producto_id,
		lista_descuentos_id: $('#lista_descuentos_id').val()
	  };

	arr_descuentos = descuentos.filter(function(item) {
		for (var key in filter_descuento) {
		  if (item[key] === undefined || item[key] != filter_descuento[key])
			return false;
		}
		return true;
	  });

	descuento = 0;
	arr_descuentos.forEach(element => {
		descuento = element.descuento1;
	});

	tasa_descuento = descuento;

	return descuento;
}

function ventana_imprimir() {

	ventana_factura = window.open("", "Impresión de factura POS", "width=400,height=600,menubar=no");

	ventana_factura.document.write($('#div_plantilla_factura').html());

	ventana_factura.print();
}


// Se llama desde el listado de productos (boton de la lupa)
function mandar_codigo(item_id) {

	//$('#myModal').modal("hide");

	var producto = productos.find(item => item.id === parseInt(item_id));

	tasa_impuesto = producto.tasa_impuesto;
	inv_producto_id = producto.id;
	unidad_medida = producto.unidad_medida1;
	costo_unitario = producto.costo_promedio;

	$('#inv_producto_id').val(producto.descripcion);
	
	$('#existencia_actual').html('Stock: ' + producto.existencia_actual.toFixed(2));
	//$('#existencia_actual').show();
	
	$('#precio_unitario').val(get_precio(producto.id));
	$('#tasa_descuento').val(get_descuento(producto.id));

	$('#cantidad').val(1);
	cantidad = 1;

	calcular_valor_descuento2();
	calcular_impuestos2();
	calcular_precio_total2();
	agregar_la_linea2();

	/*
	$('#inv_producto_id').val(producto.descripcion);
	$('#precio_unitario').val(get_precio(producto.id));

	$('#cantidad').select();
	*/
}

// Agrega la linea completa del item (Usanda en Tactil)
function mandar_codigo2(item_id) {

	var producto = productos.find(item => item.id === parseInt(item_id));

	tasa_impuesto = producto.tasa_impuesto;
	inv_producto_id = producto.id;
	unidad_medida = producto.unidad_medida1;
	costo_unitario = producto.costo_promedio;

	$('#inv_producto_id').val(producto.descripcion);
	$('#precio_unitario').val(get_precio(producto.id));
	$('#tasa_descuento').val(get_descuento(producto.id));

	cantidad = 1;
	$('#cantidad').val(cantidad);
	calcular_valor_descuento2();
	calcular_impuestos2();
	if (!calcular_precio_total2()) {
		$('#popup_alerta').show();
		$('#popup_alerta').css('background-color', 'red');
		$('#popup_alerta').text('Error en precio total. Por favor verifique');
		return false;
	}
	numero_linea = 1;
	agregar_la_linea2();
}

// Se llama desde el listado de productos (boton de la lupa)
function mandar_codigo3(item_id) {

	$('#myModal').modal("hide");

	var producto = productos.find(item => item.id === parseInt(item_id));

	tasa_impuesto = producto.tasa_impuesto;
	inv_producto_id = producto.id;
	unidad_medida = producto.unidad_medida1;
	costo_unitario = producto.costo_promedio;
		
	$('#inv_producto_id').val(producto.descripcion);
	$('#precio_unitario').val(get_precio(producto.id));
	$('#tasa_descuento').val(get_descuento(producto.id));

	$('#cantidad').select();
}
/**/

// Agrega la linea completa del item (Usanda en Filtros de items)
function mandar_codigo4(item_id) {

	var producto = productos.find(item => item.id === parseInt(item_id));

	tasa_impuesto = producto.tasa_impuesto;
	inv_producto_id = producto.id;
	unidad_medida = producto.unidad_medida1;
	costo_unitario = producto.costo_promedio;

	$('#inv_producto_id').val(producto.descripcion);
	$('#precio_unitario').val(get_precio(producto.id));
	$('#tasa_descuento').val(get_descuento(producto.id));

	var quantity = $('#quantity').val();
	
	if ($.isNumeric(quantity) ) {
		cantidad = quantity;
	}else{
		cantidad = 1;
	}

	$('#cantidad').val(cantidad);
	calcular_valor_descuento2();
	calcular_impuestos2();
	if (!calcular_precio_total2()) {
		$('#popup_alerta').show();
		$('#popup_alerta').css('background-color', 'red');
		$('#popup_alerta').text('Error en precio total. Por favor verifique');
		return false;
	}
	numero_linea = 1;
	agregar_la_linea2();
	$('#quantity').val('');
	$('#btn_guardar_factura').removeAttr('disabled');
}

function agregar_la_linea2()
{
	if ( !validar_venta_menor_costo() )
	{ 
		return false;
	}

	$('#popup_alerta').hide();

	// Se escogen los campos de la fila ingresada
	var fila = $('#linea_ingreso_default');

	var string_fila = generar_string_celdas(fila);

	if (string_fila == false) {
		$('#popup_alerta').show();
		$('#popup_alerta').css('background-color', 'red');
		$('#popup_alerta').text('Producto no encontrado.');
		return false;
	}

	// agregar nueva fila a la tabla
	$('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="' + numero_linea + '">' + string_fila + '</tr>');

	// Se calculan los totales
	calcular_totales2();

	hay_productos++;
	$('#btn_nuevo').show();
	$('#numero_lineas').text(hay_productos);
	deshabilitar_campos_encabezado2();

	reset_linea_ingreso_default2();
	$('#btn_guardar_factura').removeAttr('disabled');

	numero_linea++;
}

function reset_linea_ingreso_default2() {
	$('#inv_producto_id').val('');
	$('#cantidad').val('');
	$('#precio_unitario').val('');
	$('#tasa_descuento').val('');
	$('#tasa_impuesto').val('');
	$('#precio_total').val('');

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

function mostrar_mensaje_item_agregado()
{
	$('#popup_alerta').hide(200);
	$('#popup_alerta').css('background-color', '#00b998');
	$('#popup_alerta').css('color', 'black');
	$('#popup_alerta').css('opacity', 'revert');
	$('#popup_alerta').text('Producto agregado.');
	$('#popup_alerta').show(200);
}

function deshabilitar_campos_encabezado2() {
	$('#cliente_input').attr('disabled', 'disabled');
	$('#fecha').attr('disabled', 'disabled');
	$('#inv_bodega_id').attr('disabled', 'disabled');
}

function calcular_totales2() {
	var cantidad = 0.0;
	var subtotal = 0.0;
	var valor_total_descuento = 0.0;
	var total_impuestos = 0.0;
	total_factura = 0.0;

	$('.linea_registro').each(function () {
		cantidad += parseFloat($(this).find('.cantidad').text());
		subtotal += parseFloat($(this).find('.base_impuesto').text()) * parseFloat($(this).find('.cantidad').text());
		valor_total_descuento += parseFloat($(this).find('.valor_total_descuento').text());
		total_impuestos += parseFloat($(this).find('.valor_impuesto').text()) * parseFloat($(this).find('.cantidad').text());
		total_factura += parseFloat($(this).find('.precio_total').text());

	});

	$('#total_cantidad').text(new Intl.NumberFormat("de-DE").format(cantidad));

	// Subtotal (Sumatoria de base_impuestos por cantidad)
	//var valor = ;
	$('#subtotal').text('$ ' + new Intl.NumberFormat("de-DE").format((subtotal + valor_total_descuento).toFixed(2)));

	$('#descuento').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2)));

	// Total impuestos (Sumatoria de valor_impuesto por cantidad)
	$('#total_impuestos').text('$ ' + new Intl.NumberFormat("de-DE").format(total_impuestos.toFixed(2)));

	// label Total factura  (Sumatoria de precio_total)
	var valor_redondeado = redondear_a_centena(total_factura);
	$('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_redondeado));

	// input hidden
	$('#valor_total_factura').val(total_factura);
}

function calcular_precio_total2() {
	precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;
	$('#precio_total').val(0);

	if ($.isNumeric(precio_total) && precio_total >= 0) {
		$('#precio_total').val(precio_total);
		return true;
	} else {
		precio_total = 0;
		return false;
	}
}

// Valores unitarios
function calcular_impuestos2()
{
	var precio_venta = precio_unitario - valor_unitario_descuento;

	base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

	valor_impuesto_unitario = precio_venta - base_impuesto_unitario;

}

function calcular_valor_descuento2() {
	// El descuento se calcula cuando el precio tiene el IVA incluido
	valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
	valor_total_descuento = valor_unitario_descuento * cantidad;
}

function agregar_la_linea_ini() {
	// Se escogen los campos de la fila ingresada
	var fila = $('#linea_ingreso_default_aux');
	
	// agregar nueva fila a la tabla
	$('#ingreso_registros').find('tfoot:last').append(fila);

	$('#inv_producto_id').focus();
}

function validar_venta_menor_costo()
{
	if ( $("#permitir_venta_menor_costo").val() == 0 )
	{
		var ok = true;

		if ( base_impuesto_unitario < costo_unitario )
		{
			$('#popup_alerta').show();
			$('#popup_alerta').css('background-color','red');
			$('#popup_alerta').text( 'El precio está por debajo del costo de venta del producto.' + ' $'+ new Intl.NumberFormat("de-DE").format( costo_unitario.toFixed(2) ) + ' + IVA' );
			ok = false;
		}else{
			$('#popup_alerta').hide();
			ok = true;
		}
	}else{
		$('#popup_alerta').hide();
		ok = true;
	}

	return ok;
}

// AL CARGAR EL DOCUMENTO
$(document).ready(function () {

	$('#btn_guardar').hide();

	$('#fecha').val( get_fecha_hoy() );

	$('#fecha_vencimiento').val( format_fecha( fecha_vencimiento ) );

	agregar_la_linea_ini();

	// Elementos al final de la página
	$('#cliente_input').parent().parent().attr('style','left: 0');
	$('#cliente_input').parent().parent().attr('class', 'elemento_fondo');

	$('#cliente_input').on('focus', function () {
		$(this).select();
	});

	$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

	// Al mostrar la ventana modal
	$("#recaudoModal").on('shown.bs.modal', function () {
		$('#form_registro').before('<div id="div_pendiente_ingresar_medio_recaudo" style="color: red;">Pendiente por registrar: <span id="lbl_vlr_pendiente_ingresar">$ 0</span><div>');
		set_valor_pendiente_ingresar_medios_recaudos();
	});
	// Al OCULTAR la ventana modal
	$("#recaudoModal").on('hidden.bs.modal', function () {
		$('#div_pendiente_ingresar_medio_recaudo').remove();
	});

	// Al ingresar código, descripción o código de barras del producto
	$('#cliente_input').on('keyup', function (event) {

		var codigo_tecla_presionada = event.which || event.keyCode;

		switch (codigo_tecla_presionada) {
			case 27:// 27 = ESC
				$('#clientes_suggestions').html('');
				$('#clientes_suggestions').hide();
				break;

			case 40:// Flecha hacia abajo
				var item_activo = $("a.list-group-item.active");
				item_activo.next().attr('class', 'list-group-item list-group-item-cliente active');
				item_activo.attr('class', 'list-group-item list-group-item-cliente');
				$('#cliente_input').val(item_activo.next().html());
				break;

			case 38:// Flecha hacia arriba
				$(".flecha_mover:focus").prev().focus();
				var item_activo = $("a.list-group-item.active");
				item_activo.prev().attr('class', 'list-group-item list-group-item-cliente active');
				item_activo.attr('class', 'list-group-item list-group-item-cliente');
				$('#cliente_input').val(item_activo.prev().html());
				break;

			case 13:// Al presionar Enter

				event.preventDefault();
				
				if ($(this).val() == "") {
					return false;
				}

				var item = $("a.list-group-item.active");

				if (item.attr("data-cliente_id") === undefined) {
					alert("El cliente ingresado no existe.");
					//reset_campos_formulario();
				} else {
					console.log('Cliente seleccionado: ' + item.attr("data-cliente_id"));
					seleccionar_cliente(item);
				}
				break;

			default:
				// Manejo código de producto o nombre
				var campo_busqueda = 'descripcion';
				if ($.isNumeric($(this).val())) {
					var campo_busqueda = 'numero_identificacion';
				}

				// Si la longitud es menor a tres, todavía no busca
				if ($(this).val().length < 2) {
					return false;
				}

				var url = '../vtas_consultar_clientes';

				$.get(url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda, url_id: $('#url_id').val() })
					.done(function (data) {
						// Se llena el DIV con las sugerencias que arroja la consulta
						$('#clientes_suggestions').show().html(data);
						$('a.list-group-item.active').focus();
					});
				break;
		}
	});

	$('.btn_vendedor').on('click', function (e) {
		e.preventDefault();

		$('.vendedor_activo').attr('class','btn btn-default btn_vendedor');

		$(this).attr('class','btn btn-default btn_vendedor vendedor_activo');

		$('#btn_guardar_factura').focus();

		$('#vendedor_id').val( $(this).attr('data-vendedor_id') );
		$('#vendedor_id').attr( 'data-vendedor_descripcion', $(this).attr('data-vendedor_descripcion') );
        $(document).prop('title', $(this).attr('data-vendedor_descripcion'));
		
	});
	
});