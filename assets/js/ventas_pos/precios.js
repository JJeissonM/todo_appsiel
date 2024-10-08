var  precio_total,
    costo_total,
    cantidad,
    base_impuesto_total,
    valor_impuesto_total,
    tasa_impuesto,
    tasa_descuento,
    valor_total_descuento,
    total_factura,
    filter_descuento;

var base_impuesto_unitario = 0;
var valor_impuesto_unitario = 0;
var valor_unitario_descuento = 0;
var total_cambio = 0;
var valor_ajuste_al_peso = 0;
var costo_unitario = 0;
var precio_unitario = 0;

/**
 * 
 * @returns boolean
 */
function calcular_precio_total() {

    if (cantidad <= 0 || cantidad == undefined) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text("No ha ingresado Cantidad.");
        return false;
    }

    if ( precio_unitario == 0 ) {
        Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "Precio Unitario no puede ser cero (0).",
        });
        return false;
    }

    // Descuento del 100%
    if (precio_unitario == valor_unitario_descuento) {
        precio_total = 0;
        $("#precio_total").val(precio_total);
        $("#popup_alerta").hide();
        return true;
    }

    precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

    $("#precio_total").val(0);

    if ( $('#permitir_precio_unitario_negativo').val() == 0) {
        if ( precio_total < 0) {            

            Swal.fire({
                icon: "error",
                title: "Alerta!",
                text: "Precio Unitario no puede ser Negativo.",
                });

            precio_total = 0;
            return false;
        }
    }
    
    if ( $.isNumeric(precio_total) ) {
        $("#precio_total").val(precio_total);
        return true;
    } else {
        $("#popup_alerta").hide();
        precio_total = 0;
        return false;
    }
}

/**
 * El descuento se calcula cuando el precio tiene el IVA incluido
 */
function calcular_valor_descuento() 
{
    valor_unitario_descuento = (precio_unitario * tasa_descuento) / 100;
    valor_total_descuento = valor_unitario_descuento * cantidad;
}

  
/**
 * Set Valores unitarios
 */
function calcular_impuestos()
{
    var precio_venta = precio_unitario - valor_unitario_descuento;

    base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

    valor_impuesto_unitario = precio_venta - base_impuesto_unitario;
}

/**
 * 
 */
function calcular_totales() {
    var total_cantidad = 0.0;
    var subtotal = 0.0;
    var valor_total_descuento = 0.0;
    var total_impuestos = 0.0;
    total_factura = 0.0;

    $(".linea_registro").each(function () {
        var cantidad_linea = parseFloat(
        $(this).find(".elemento_modificar").eq(0).text()
        );
        total_cantidad += cantidad_linea;

        subtotal +=
        parseFloat($(this).find(".base_impuesto").text()) * cantidad_linea;
        valor_total_descuento += parseFloat(
        $(this).find(".valor_total_descuento").text()
        );
        total_impuestos +=
        parseFloat($(this).find(".valor_impuesto").text()) * cantidad_linea;
        total_factura += parseFloat($(this).find(".precio_total").text());
    });

    $("#total_cantidad").text(
        new Intl.NumberFormat("de-DE").format(total_cantidad)
    );

    // Subtotal (Sumatoria de base_impuestos por cantidad)
    //var valor = ;
    $("#subtotal").text(
        "$ " +
        new Intl.NumberFormat("de-DE").format(
            (subtotal + valor_total_descuento).toFixed(2)
        )
    );

    $("#descuento").text(
        "$ " +
        new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2))
    );

    // Total impuestos (Sumatoria de valor_impuesto por cantidad)
    $("#total_impuestos").text(
        "$ " + new Intl.NumberFormat("de-DE").format(total_impuestos.toFixed(2))
    );

    // label Total factura  (Sumatoria de precio_total)
    var valor_redondeado = redondear_a_centena(total_factura);

    $("#total_factura").text(
        "$ " + new Intl.NumberFormat("de-DE").format(valor_redondeado)
    );

    // input hidden
    $("#valor_total_factura").val(total_factura);

    valor_ajuste_al_peso = valor_redondeado - total_factura;

    $("#lbl_ajuste_al_peso").text(
        "$ " + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso)
    );

    if ($("#manejar_propinas").val() == 1) {
        $.fn.calcular_valor_a_pagar_propina(total_factura);

        $.fn.calcular_totales_propina();
    }

    if (
        $("#manejar_datafono").val() == 1 &&
        $("#calcular_comision_datafono").is(":checked")
    ) {
        $.fn.calcular_valor_a_pagar_datafono(total_factura);

        $.fn.calcular_totales_datafono();
    }

    $("#valor_sub_total_factura").val(total_factura);
    $("#lbl_sub_total_factura").text(
        "$ " + new Intl.NumberFormat("de-DE").format(total_factura)
    );
}

/**
 * 
 * @param {*} fila 
 */
function calcular_precio_total_lbl(fila)
{
    tasa_descuento = parseFloat(fila.find(".tasa_descuento").text());
    cantidad = parseFloat(fila.find(".elemento_modificar").eq(0).text());
    precio_unitario = parseFloat(fila.find(".elemento_modificar").eq(1).text());

    var tasa_impuesto = parseFloat(fila.find(".lbl_tasa_impuesto").text());

    valor_unitario_descuento = (precio_unitario * tasa_descuento) / 100;
    valor_total_descuento = valor_unitario_descuento * cantidad;

    var precio_venta = precio_unitario - valor_unitario_descuento;
    base_impuesto_unitario = precio_venta / (1 + tasa_impuesto / 100);

    precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;

    console.log( precio_unitario, valor_unitario_descuento, precio_total ) 

    fila.find(".precio_unitario").text(precio_unitario);

    fila.find(".base_impuesto").text(base_impuesto_unitario);

    fila.find(".valor_impuesto").text(precio_venta - base_impuesto_unitario);

    fila.find(".cantidad").text(cantidad);

    fila.find(".precio_total").text(precio_total);

    fila.find(".base_impuesto_total").text(base_impuesto_unitario * cantidad);

    fila.find(".valor_total_descuento").text(valor_total_descuento);

    fila
      .find(".lbl_valor_total_descuento")
      .text(
        new Intl.NumberFormat("de-DE").format(valor_total_descuento.toFixed(2))
      );

    fila
      .find(".lbl_precio_total")
      .text(new Intl.NumberFormat("de-DE").format(precio_total.toFixed(2)));
}

/**
 * 
 * @returns boolean
 */
function validar_venta_menor_costo()
{
    if ($("#permitir_venta_menor_costo").val() == 1)
    {
      $("#popup_alerta").hide();
      return true;
    }
  
    if (base_impuesto_unitario >= costo_unitario)
    {
        $("#popup_alerta").hide();
        return true;
    }

    $("#popup_alerta").show();
    $("#popup_alerta").css("background-color", "red");
    $("#popup_alerta").text(
        "El precio está por debajo del costo de venta del producto." +
        " $" +
        new Intl.NumberFormat("de-DE").format(costo_unitario.toFixed(2)) +
        " + IVA"
    );
  
    return false;
}

/**
 * 
 * @param {*} producto_id 
 * @returns precio
 */
function get_precio(producto_id) {

  var precio = precios.find((item) => item.producto_codigo === producto_id);

  if (precio === undefined) {
    precio = 0;
  } else {
    precio = precio.precio;
  }

  precio_unitario = precio;

  return precio;
}

/**
 * 
 * @param {*} producto_id 
 * @returns descuento
 */
function get_descuento(producto_id) {
  filter_descuento = {
    producto_codigo: producto_id,
    lista_descuentos_id: $("#lista_descuentos_id").val(),
  };

  arr_descuentos = descuentos.filter(function (item) {
    for (var key in filter_descuento) {
      if (item[key] === undefined || item[key] != filter_descuento[key])
        return false;
    }
    return true;
  });

  descuento = 0;
  arr_descuentos.forEach((element) => {
    descuento = element.descuento1;
  });

  tasa_descuento = descuento;

  return descuento;
}

/**
 * 
 */
function set_precios_lbl_items()
{
    $(".lbl_precio_item").each(function () {
        
        var item_id = parseInt( $(this).attr('data-item_id') );

        $(this).text( '$' + new Intl.NumberFormat("de-DE").format( get_precio(item_id).toFixed(0) ) );    
    });
}

/**
 * 
 */
function set_lista_precios()
{
  $.get(
    url_raiz +
      "/vtas_get_lista_precios_cliente" +
      "/" +
      $("#cliente_id").val()
  ).done(function (data) {
    precios = data[0];
    descuentos = data[1];
    set_precios_lbl_items();
  });
}


function calcular_totales_quantity() 
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
    var valor_redondeado = redondear_a_centena(total_factura);
    $('#total_factura').text('$ ' + new Intl.NumberFormat("de-DE").format(valor_redondeado));

    // input hidden
    $('#valor_total_factura').val(total_factura);

    valor_ajuste_al_peso = valor_redondeado - total_factura;

    $('#lbl_ajuste_al_peso').text( '$ ' + new Intl.NumberFormat("de-DE").format(valor_ajuste_al_peso));
}

function calcular_precio_total_lbl_quantity(fila) 
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

	if (saldo2 > 99.99999) {
		// se obtiene solo la parte entera
		//centenas = Math.trunc( saldo2 / 100 ) * 100;
		centenas = (saldo2 / 100).toFixed(0) * 100;
	}

	return (millones + millares + centenas);

};