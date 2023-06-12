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

$('#teso_motivo_id').val( 1 );
$('#teso_caja_id').val( $('#caja_pdv_default_id').val() );

// Se debe llamar desde el DIV con ID total_valor_total
$.fn.actualizar_medio_recaudo = function(){
    
    var texto_total_recaudos = this.html().substring(1);
    
    if( parseFloat( texto_total_recaudos ) == 0 )
    {
        return false;
    }

    $.fn.calcular_total_cambio( texto_total_recaudos );

    $('#efectivo_recibido').val( parseFloat( texto_total_recaudos ) );
    $('#total_efectivo_recibido').val( parseFloat( texto_total_recaudos ) );
    $('#efectivo_recibido').attr( 'readonly', 'readonly' );

    $.fn.set_label_efectivo_recibido( texto_total_recaudos );

    $.fn.cambiar_estilo_div_total_cambio();

    $.fn.activar_boton_guardar_factura();

};

$.fn.calcular_totales_aux = function() 
{
    var cantidad = 0.0;
    var subtotal = 0.0;
    var valor_total_descuento = 0.0;
    var total_impuestos = 0.0;
    total_factura = 0.0;

    $('.linea_registro').each(function() {
        cantidad += parseFloat( $(this).find('.cantidad').text() );
        subtotal += parseFloat( $(this).find('.base_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
        valor_total_descuento += parseFloat( $(this).find('.valor_total_descuento').text() );
        total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() ) * parseFloat( $(this).find('.cantidad').text() );
        total_factura += parseFloat( $(this).find('.precio_total').text() );
    });

    $('#total_cantidad').text( new Intl.NumberFormat("de-DE").format(cantidad));

    // Subtotal (Sumatoria de base_impuestos por cantidad)
    //var valor = ;
    $('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( (subtotal + valor_total_descuento).toFixed(2) ) );

    $('#descuento').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2)));

    // Total impuestos (Sumatoria de valor_impuesto por cantidad)
    $('#total_impuestos').text('$ ' + new Intl.NumberFormat("de-DE").format(total_impuestos.toFixed(2)));

    // label Total factura  (Sumatoria de precio_total)
    var valor_redondeado = $.fn.redondear_a_centena_aux(total_factura);
    console.log([valor_redondeado]);
    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_redondeado));

    // input hidden
    $('#valor_total_factura').val(total_factura);

    valor_ajuste_al_peso = valor_redondeado - total_factura;

    $('#lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso));
};

$.fn.redondear_a_centena_aux = function(numero, aproximacion_superior = false) 
{
    if (!redondear_centena) 
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

    if (saldo2 > 99.99999) {
        // se obtiene solo la parte entera
        //centenas = Math.trunc( saldo2 / 100 ) * 100;
        centenas = (saldo2 / 100).toFixed(0) * 100;
    }

    return (millones + millares + centenas);

};


$.fn.deshabilitar_campos_encabezado_aux = function() 
{
    $('#cliente_input').attr('disabled', 'disabled');
    $('#fecha').attr('disabled', 'disabled');
    $('#inv_bodega_id').attr('disabled', 'disabled');
};


$.fn.reset_linea_ingreso_default_aux = function()
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
};


$.fn.reset_efectivo_recibido_aux = function()
{
    $('#efectivo_recibido').val('');
    $('#total_efectivo_recibido').val(0);
    $('#lbl_efectivo_recibido').text('$ 0');
    $('#total_cambio').text('$ 0');
    $('#lbl_ajuste_al_peso').text('$ ');
    total_cambio = 0;
    $('#btn_guardar_factura').attr('disabled', 'disabled');
};

// 
$.fn.calcular_total_cambio = function( efectivo_recibido )
{
    
    total_cambio = ( $.fn.redondear_a_centena( parseFloat( $('#valor_total_factura').val() ) ) - parseFloat( efectivo_recibido ) ) * -1;
    
    // Label
    $('#total_cambio').text('$ ' + new Intl.NumberFormat("de-DE").format(total_cambio.toFixed(0)));
    // Input hidden
    $('#valor_total_cambio').val(total_cambio);
};

$.fn.set_label_efectivo_recibido = function( efectivo_recibido )
{
    $('#lbl_efectivo_recibido').text('$ ' + new Intl.NumberFormat("de-DE").format( parseFloat( efectivo_recibido ).toFixed(2) ) );
};

$.fn.cambiar_estilo_div_total_cambio = function(){
    
    $('#div_total_cambio').attr('class', 'alert alert-danger');

    if (total_cambio.toFixed(0) >= 0)
        $('#div_total_cambio').attr('class', 'alert alert-success');
};

$.fn.activar_boton_guardar_factura = function(){
    
    $('#btn_guardar_factura').attr('disabled', 'disabled');

    if (total_cambio.toFixed(0) >= 0)
        $('#btn_guardar_factura').removeAttr('disabled'); 

};



$.fn.checkCookie = function()
{
    var ultimo_valor_total_factura = parseFloat( $.fn.getCookie("ultimo_valor_total_factura"));
    var ultimo_valor_efectivo_recibido = parseFloat( $.fn.getCookie("ultimo_valor_efectivo_recibido"));
    var ultimo_valor_total_cambio = parseFloat( $.fn.getCookie("ultimo_valor_total_cambio"));
    var ultimo_valor_ajuste_al_peso = parseFloat( $.fn.getCookie("ultimo_valor_ajuste_al_peso"));

    if (ultimo_valor_total_factura > 0)
    {
        $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format( $.fn.redondear_a_centena(ultimo_valor_total_factura ) ) );
        $('#lbl_efectivo_recibido').text('$ ' + new Intl.NumberFormat("de-DE").format(ultimo_valor_efectivo_recibido.toFixed(2)));
        $('#total_cambio').text('$ ' + new Intl.NumberFormat("de-DE").format((ultimo_valor_total_cambio)));
        $('#lbl_ajuste_al_peso').text('$ ' + new Intl.NumberFormat("de-DE").format(ultimo_valor_ajuste_al_peso));
    }
       
    $("html, body").animate({scrollTop: $(document).height() + "px"});
};


$.fn.getCookie = function(cname)
{
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

$.fn.redondear_a_centena = function(numero, aproximacion_superior = false) 
{
    if (!redondear_centena) 
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

    if (saldo2 > 99.99999) {
        // se obtiene solo la parte entera
        //centenas = Math.trunc( saldo2 / 100 ) * 100;
        centenas = (saldo2 / 100).toFixed(0) * 100;
    }

    return (millones + millares + centenas);

};

// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
// Debe ser complatible con las columnas de la tabla de ingreso de registros
$.fn.generar_string_celdas = function( fila )
{
	if ( inv_producto_id === undefined )
	{
		return false;
	}

	var celdas = [];
	var num_celda = 0;

	celdas[ num_celda ] = '<td style="display: none;"><div class="inv_producto_id">'+ inv_producto_id +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="precio_unitario">'+ precio_unitario +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto">'+ base_impuesto_unitario +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_impuesto">'+ tasa_impuesto +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="valor_impuesto">'+ valor_impuesto_unitario +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto_total">'+ base_impuesto_unitario * cantidad +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="cantidad">'+ cantidad +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="precio_total">'+ precio_total +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_descuento">'+ tasa_descuento +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td style="display: none;"><div class="valor_total_descuento">'+ valor_total_descuento +'</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td> &nbsp; </td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ inv_producto_id + '</span> <div class="lbl_producto_descripcion" style="display: inline;"> ' + $('#inv_producto_id').val() + ' </div> </td>';
	
	num_celda++;

	//celdas[ num_celda ] = '<td>' + cantidad + ' </td>';
	celdas[ num_celda ] = '<td> <div style="display: inline;"> <div class="elemento_modificar" title="Doble click para modificar."> ' + cantidad + '</div> </div>  (<div class="lbl_producto_unidad_medida" style="display: inline;">' + unidad_medida + '</div>)' + ' </td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td> <div class="lbl_precio_unitario" style="display: inline;">'+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_unitario ) + '</div></td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td>'+ tasa_descuento + '% ( $<div class="lbl_valor_total_descuento" style="display: inline;">' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(0) ) + '</div> ) </td>';
	
	num_celda++;

	celdas[ num_celda ] = '<td><div class="lbl_tasa_impuesto" style="display: inline;">'+ tasa_impuesto + '%</div></td>';
	
	num_celda++;

	var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
	celdas[ num_celda ] = '<td> <div class="lbl_precio_total" style="display: inline;">' + '$ ' + new Intl.NumberFormat("de-DE").format( precio_total.toFixed(0) ) + ' </div> </td> <td>' + btn_borrar + '</td>';

	var cantidad_celdas = celdas.length;
	var string_celdas = '';
	for (var i = 0; i < cantidad_celdas; i++)
	{
		string_celdas = string_celdas + celdas[i];
	}

	inv_producto_id = undefined; // para que no quede en memoria el código del producto

	return string_celdas;
};

function get_precio( producto_id )
{
    var precio = precios.find( item => item.producto_codigo === producto_id);

    if (precio === undefined)
    {
        precio = 0;
    } else {
        precio = precio.precio;
    }


    precio_unitario = precio;

    return precio;
}

function get_descuento( producto_id )
{
    var descuento = descuentos.find( item => item.producto_codigo === producto_id);

    if (descuento === undefined)
    {
        descuento = 0;
    } else {
        descuento = descuento.descuento1;
    }

    tasa_descuento = descuento;

    return descuento;
}

function ventana_imprimir()
{

    ventana_factura = window.open("", "Impresión de factura POS", "width=400,height=600,menubar=no");

    ventana_factura.document.write($('#div_plantilla_factura').html());

    ventana_factura.print();
}

function mandar_codigo(item_id)
{
    $('#myModal').modal("hide");

    var producto = productos.find(item => item.id === parseInt(item_id));

    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;

    $('#inv_producto_id').val(producto.descripcion);
    $('#precio_unitario').val(get_precio(producto.id));
    $('#tasa_descuento').val(get_descuento(producto.id));

    $('#cantidad').select();
}




$(document).ready(function () {

	$.fn.checkCookie();

	$('#btn_guardar').hide();

	agregar_la_linea_ini();

	// Elementos al final de la página
	$('#cliente_input').parent().parent().attr('class','elemento_fondo');
	$('#vendedor_id').parent().parent().attr('class','elemento_fondo');


    $('#cliente_input').on('focus', function () {
        $(this).select();
    });

    $("#cliente_input").after('<div id="clientes_suggestions"> </div>');

	// Al ingresar código, descripción o código de barras del producto
    $('#cliente_input').on('keyup',function(){

    	reset_campos_formulario();

    	var codigo_tecla_presionada = event.which || event.keyCode;

    	switch( codigo_tecla_presionada )
    	{
    		case 27:// 27 = ESC
				$('#clientes_suggestions').html('');
            	$('#clientes_suggestions').hide();
    			break;

    		case 40:// Flecha hacia abajo
				var item_activo = $("a.list-group-item.active");					
				item_activo.next().attr('class','list-group-item list-group-item-cliente active');
				item_activo.attr('class','list-group-item list-group-item-cliente');
				$('#cliente_input').val( item_activo.next().html() );
    			break;

    		case 38:// Flecha hacia arriba
				$(".flecha_mover:focus").prev().focus();
				var item_activo = $("a.list-group-item.active");					
				item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
				item_activo.attr('class','list-group-item list-group-item-cliente');
				$('#cliente_input').val( item_activo.prev().html() );
    			break;

    		case 13:// Al presionar Enter

    			if ( $(this).val() == '' )
				{
					return false;
				}

				var item = $('a.list-group-item.active');
				
				if( item.attr('data-cliente_id') === undefined )
				{
					alert('El cliente ingresado no existe.');
					reset_campos_formulario();
				}else{
					seleccionar_cliente( item );
				}
    			break;

    		default :
	    		// Manejo código de producto o nombre
	    		var campo_busqueda = 'descripcion';
	    		if( $.isNumeric( $(this).val() ) ){
		    		var campo_busqueda = 'numero_identificacion';
		    	}

		    	// Si la longitud es menor a tres, todavía no busca
			    if ( $(this).val().length < 2 )
			    { 
			    	return false;
			    }

		    	var url = '../vtas_consultar_clientes';

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arooja la consulta
		                $('#clientes_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
    			break;
    	}
    });

	function agregar_la_linea_ini()
	{
		// Se escogen los campos de la fila ingresada
		var fila = $('#linea_ingreso_default_aux');

		// agregar nueva fila a la tabla
		$('#ingreso_registros').find('tfoot:last').append( fila );

		$('#inv_producto_id').focus();
	}

	function reset_campos_formulario()
	{
		$('#cliente_id').val( '' );
		$('#cliente_input').css( 'background-color','#FF8C8C' );
        $('#vendedor_id').val( '' );
        $('#inv_bodega_id').val( '' );
        $('#forma_pago').val( 'contado' );
		$('#fecha_vencimiento').val( '' );
        $('#lista_precios_id').val( '' );
        $('#lista_descuentos_id').val( '' );
        $('#liquida_impuestos').val( '' );

        $('#core_tercero_id').val( '' );
        $('#lineas_registros').val( 0 );
        $('#zona_id').val( '' );
        $('#clase_cliente_id').val( '' );
	}
});