// Variables de cada línea de ingresos de registros.
var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, tasa_descuento, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id, unidad_medida, total_factura;
var costo_unitario = 0;
var precio_unitario = 0;
var base_impuesto_unitario = 0;
var valor_impuesto_unitario = 0;
var valor_unitario_descuento = 0;
var total_cambio = 0;
var valor_ajuste_al_peso = 0;
var ventana_factura;
var numero_linea = 0;

// Se debe llamar desde el DIV con ID total_valor_total
$.fn.actualizar_medio_recaudo = function () {
	var texto_total_recaudos = parseFloat(this.html().substring(1));
  
	calcular_total_cambio(texto_total_recaudos);
  
	$("#efectivo_recibido").val(texto_total_recaudos);
	$("#total_efectivo_recibido").val(texto_total_recaudos);
  
	set_label_efectivo_recibido(texto_total_recaudos);
  
	cambiar_estilo_div_total_cambio();
  
	activar_boton_guardar_factura();
  
	if (texto_total_recaudos == 0) {
	  $("#efectivo_recibido").val("");
	  $("#efectivo_recibido").removeAttr("readonly");
	} else {
	  $("#efectivo_recibido").attr("readonly", "readonly");
	}
  
	set_valor_pendiente_ingresar_medios_recaudos();
  };


function set_valor_pendiente_ingresar_medios_recaudos() {
	var valor_total_factura = parseFloat($("#valor_total_factura").val());

	var valor_total_lineas_medios_recaudos = parseFloat(
		$("#total_valor_total").html().substring(1)
	);

	$("#lbl_vlr_pendiente_ingresar").html(
		"$ " + (valor_total_factura - valor_total_lineas_medios_recaudos).toFixed(2)
	);
};

// 
function calcular_total_cambio(efectivo_recibido)
{

	total_cambio = ( redondear_a_centena( parseFloat( $('#valor_total_factura').val() ) ) - parseFloat(efectivo_recibido) ) * -1;

	// Label
	$('#total_cambio').text('$ ' + new Intl.NumberFormat("de-DE").format(total_cambio.toFixed(0)));
	// Input hidden
	$('#valor_total_cambio').val(total_cambio);
};

function set_label_efectivo_recibido(efectivo_recibido) {
	$('#lbl_efectivo_recibido').text('$ ' + new Intl.NumberFormat("de-DE").format(parseFloat(efectivo_recibido).toFixed(2)));
};

function cambiar_estilo_div_total_cambio() {

	$('#div_total_cambio').attr('class', 'danger');

	if (total_cambio.toFixed(0) >= 0)
		$('#div_total_cambio').attr('class', 'success');
};

function checkCookie() {
	var ultimo_valor_total_factura = parseFloat(getCookie("ultimo_valor_total_factura"));
	var ultimo_valor_efectivo_recibido = parseFloat(getCookie("ultimo_valor_efectivo_recibido"));
	var ultimo_valor_total_cambio = parseFloat(getCookie("ultimo_valor_total_cambio"));
	var ultimo_valor_ajuste_al_peso = parseFloat(getCookie("ultimo_valor_ajuste_al_peso"));

	if (ultimo_valor_total_factura > 0) {
		$('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format(redondear_a_centena(ultimo_valor_total_factura)));
		$('#lbl_efectivo_recibido').text('$ ' + new Intl.NumberFormat("de-DE").format(ultimo_valor_efectivo_recibido.toFixed(2)));
		$('#total_cambio').text('$ ' + new Intl.NumberFormat("de-DE").format((ultimo_valor_total_cambio)));
		$('#lbl_ajuste_al_peso').text('$ ' + new Intl.NumberFormat("de-DE").format(ultimo_valor_ajuste_al_peso));
	}

	//$("html, body").animate({ scrollTop: $(document).height() + "px" });
};

function getCookie(cname) {
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for (var i = 0; i < ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0) == ' ') {
			c = c.substring(1);
		}
		if (c.indexOf(name) == 0) {
			return c.substring(name.length, c.length);
		}
	}
	return "";
};

// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
// Debe ser complatible con las columnas de la tabla de ingreso de registros
function generar_string_celdas()
{
	if (inv_producto_id === undefined) {
		return false;
	}

	var celdas = [];
	var num_celda = 0;

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

	celdas[num_celda] = '<td style="display: none;"><div class="lista_oculta_items_contorno_ids"></div></td>';

	num_celda++;

	celdas[num_celda] = '<td> &nbsp; </td>';

	num_celda++;

	var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";

	var producto = productos.find(item => item.id === parseInt(inv_producto_id));

	var btn_add_contorno = ''
	var arr_categorias = $('#categoria_id_platillos_con_contornos').val().split(',').map(Number);
	
	if ( arr_categorias.includes( producto.inv_grupo_id ) ) {
		btn_add_contorno = "<button type='button' class='btn btn-primary btn-xs btn_add_contorno'><i class='fa fa-btn fa-plus'></i></button>";
	}	

	celdas[num_celda] = '<td> ' + btn_borrar + btn_add_contorno + ' &nbsp;&nbsp; <div class="lbl_producto_descripcion" style="display: inline;"> ' + $('#inv_producto_id').val() + ' </div> </td>';

	num_celda++;

	//celdas[ num_celda ] = '<td>' + cantidad + ' </td>';
	celdas[num_celda] = '<td> <div class="number-input"><button onclick="this.parentNode.querySelector(\'input[type=number]\').stepDown()" class="minus"></button><input class="quantity" min="1" name="quantity" value="1" type="number"><button onclick="this.parentNode.querySelector(\'input[type=number]\').stepUp()" class="plus"></button> </div> </td>';

	num_celda++;

	celdas[num_celda] = '<td> <div class="lbl_precio_unitario" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(precio_unitario) + '</div></td>';
	//celdas[num_celda] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar.">' + precio_unitario + '</div> </div> </td>';

	num_celda++;

	celdas[num_celda] = '<td>' + tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(0)) + '</div> ) </td>';

	num_celda++;

	celdas[num_celda] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">' + tasa_impuesto + '</div></td>';

	num_celda++;

	celdas[num_celda] = '<td> <div class="lbl_precio_total" style="display: inline;">' + '$' + new Intl.NumberFormat("de-DE").format(precio_total.toFixed(0)) + ' </div> </td> <td> &nbsp; </td>';

	var cantidad_celdas = celdas.length;
	var string_celdas = '';
	for (var i = 0; i < cantidad_celdas; i++) {
		string_celdas = string_celdas + celdas[i];
	}

	inv_producto_id = undefined; // para que no quede en memoria el código del producto

	return string_celdas;
};

function reset_campos_formulario()
{
	$('#fecha').val( get_fecha_hoy() );
	$('#descripcion').val( '' );

	$('#cliente_id').val( cliente_default.id );
	$('#cliente_input').val( cliente_default.descripcion );
	$('#cliente_input').css('background-color', 'transparent');
	//$('#vendedor_id').val( cliente_default.vendedor_id );
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
	calcular_totales();

	hay_productos++;
	$('#btn_nuevo').show();
	$('#numero_lineas').text(hay_productos);
	set_cantidades_ingresadas();
	deshabilitar_campos_encabezado2();

	reset_linea_ingreso_default2();
	reset_efectivo_recibido2();

	numero_linea++;	

	if ($('#manejar_platillos_con_contorno').val() == 1) {
		reset_component_items_contorno();
	}
}

function reset_efectivo_recibido2()
{
	$('#efectivo_recibido').val('');
	$('#total_efectivo_recibido').val(0);
	$('#lbl_efectivo_recibido').text('$ 0');
	$('#total_cambio').text('$ 0');
	$('#lbl_ajuste_al_peso').text('$ ');
	total_cambio = 0;
}

function reset_linea_ingreso_default2() {
	$('#inv_producto_id').val('');
	$('#cantidad').val('');
	$('#precio_unitario').val('');
	$('#tasa_descuento').val('');
	$('#tasa_impuesto').val('');
	$('#precio_total').val('');

	//$('#inv_producto_id').focus();

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

function deshabilitar_campos_encabezado2() {
	$('#fecha').attr('disabled', 'disabled');
	$('#inv_bodega_id').attr('disabled', 'disabled');
}

/**
 * 
 */
function bloquear_mesas_pedidos_otros_meseros()
{
	var url = url_raiz + "/" + "vtas_pedidos_restaurante_mesas_disponibles_mesero" + "/" + $('#vendedor_id').val();

	$.get(url, function (disponibles) {
		var arr_disponibles = [];
		var i = 0;
		disponibles.forEach(disponible => {
			arr_disponibles[i] = parseInt(disponible.mesa_id);
			i++;
		});
		
		$('.btn_mesa').each(function () {
			if ( !arr_disponibles.includes( parseInt($(this).attr('data-mesa_id') ) ) ) {
				$(this).attr('disabled','disabled');
			}			
		});
	});
}

/**
 * 
 */
function mostrar_mensaje_item_agregado() {
	$("#popup_alerta").hide(200);
	$("#popup_alerta").css("background-color", "#00b998");
	$("#popup_alerta").css("color", "black");
	$("#popup_alerta").css("opacity", "revert");
	$("#popup_alerta").text("Producto agregado.");
	$("#popup_alerta").show(200);
  }
  

  
$(document).ready(function () {

	$(document).on('click', '.minus', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		calcular_precio_total_lbl_quantity(fila);
		calcular_totales();
	});

	$(document).on('click', '.plus', function(event) {
		event.preventDefault();
		var fila = $(this).closest("tr");
		calcular_precio_total_lbl_quantity(fila);
		calcular_totales();
	});
	
});