
var dias_plazo, dias_plazo_original;
// Variables de cada línea de ingresos de registros.
var producto_id, precio_total, costo_total, base_impuesto_total, valor_impuesto_total, tasa_impuesto, valor_total_descuento, cantidad, inv_producto_id, inv_bodega_id, inv_motivo_id;
var costo_unitario = 0;
var precio_unitario = 0;
var base_impuesto_unitario = 0;
var valor_impuesto_unitario = 0;
var valor_unitario_descuento = 0;
var tasa_descuento = 0;

var hay_productos = 0;

var ya_esta;

$.fn.actualizar_medio_recaudo = function(){

    var texto_total_recaudos = this.html().substring(1);
    
    if( parseFloat( texto_total_recaudos ) == 0 )
    {
        return false;
    }

};

/**
 * 
 */
function calcular_totales()
{	
    var cantidad = 0.0;
    var subtotal = 0.0;
    var valor_total_descuento = 0.0;
    var total_impuestos = 0.0;
    var total_factura = 0.0;
    $('.linea_registro').each(function()
    {
        cantidad += parseFloat( $(this).find('.cantidad').text() );
        subtotal += parseFloat( $(this).find('.base_impuesto').text() );
        valor_total_descuento += parseFloat( $(this).find('.valor_total_descuento').text() );
        total_impuestos += parseFloat( $(this).find('.valor_impuesto').text() );
        total_factura += parseFloat( $(this).find('.precio_total').text() );

    });
    $('#total_cantidad').text( new Intl.NumberFormat("de-DE").format( cantidad ) );

    // Subtotal (Sumatoria de base_impuestos por cantidad)
    //var valor = ;
    $('#subtotal').text( '$ ' + new Intl.NumberFormat("de-DE").format( (subtotal + valor_total_descuento).toFixed(2) )  );

    $('#descuento').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(2) )  );

    // Total impuestos (Sumatoria de valor_impuesto por cantidad)
    $('#total_impuestos').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_impuestos.toFixed(2) ) );

    // Total factura  (Sumatoria de precio_total)
    $('#total_factura').text( '$ ' + new Intl.NumberFormat("de-DE").format( total_factura.toFixed(2) ) );
    
}

/**
 * 
 * @param {*} item 
 */
function tecla_enter_on_keyup( item )
{
    if( item.attr('data-proveedor_id') === undefined )
    {
        alert('El proveedor ingresado no existe.');
        reset_campos_formulario();
    }else{
        seleccionar_proveedor( item );
    }
}

/**
 * 
 * @returns 
 */
function validar_documento_proveedor()
{
    url = '../compras_validar_documento_proveedor';
    $.get( url, { proveedor_id: $('#proveedor_id').val(), doc_proveedor_prefijo: $('#doc_proveedor_prefijo').val(), doc_proveedor_consecutivo: $('#doc_proveedor_consecutivo').val() } )
        .done(function( data ) {

            if( data == 'true' )
            {
                ya_esta = true;
                $('#doc_proveedor_consecutivo').focus();
                $('#popup_alerta').show();
                $('#popup_alerta').css('background-color','red');
                $('#popup_alerta').text( 'Ya existe una factura ingresada para el proveedor con ese prefijo y consecutivo: ' + $('#doc_proveedor_prefijo').val() + ' - ' + $('#doc_proveedor_consecutivo').val() );
            }else{
                ya_esta = false;
            }
        });

    return ya_esta;
}

// Valores unitarios
function calcular_impuestos()
{
    var precio_compra = precio_unitario - valor_unitario_descuento;

    base_impuesto_unitario = precio_compra / ( 1 + tasa_impuesto / 100 );

    valor_impuesto_unitario = precio_compra - base_impuesto_unitario;

    costo_unitario = base_impuesto_unitario;
}

function calcular_valor_descuento()
{
    // El descuento se calcula cuando el precio tiene el IVA incluido
    valor_unitario_descuento = precio_unitario * tasa_descuento / 100;
    $('#valor_unitario_descuento').val( valor_unitario_descuento );

    valor_total_descuento = valor_unitario_descuento * cantidad;
    $('#valor_total_descuento').val( valor_total_descuento );
}


function reset_descuento()
{
    $('#tasa_descuento').val( 0 );
    calcular_valor_descuento();
}
			

function seleccionar_proveedor(item_sugerencia)
{
    // Asignar descripción al TextInput
    $('#proveedor_input').val( item_sugerencia.html() );
    $('#proveedor_input').css( 'background-color','white ' );

    // Asignar Campos ocultos
    $('#proveedor_id').val( item_sugerencia.attr('data-proveedor_id') );
    $('#clase_proveedor_id').val( item_sugerencia.attr('data-clase_proveedor_id') );
    $('#liquida_impuestos').val( item_sugerencia.attr('data-liquida_impuestos') );
    $('#core_tercero_id').val( item_sugerencia.attr('data-core_tercero_id') );

    // Asignar resto de campos
    $('#vendedor_id').val( item_sugerencia.attr('data-vendedor_id') );

    var bodega_id = item_sugerencia.attr('data-inv_bodega_id');
    if ( bodega_id == 0 ) {
        bodega_id = $('#inv_bodega_default_id').val();
    }

    $('#inv_bodega_id').val( bodega_id );

    var forma_pago = 'contado';
    dias_plazo = parseInt( item_sugerencia.attr('data-dias_plazo') );
    dias_plazo_original = parseInt( item_sugerencia.attr('data-dias_plazo') );
    if ( dias_plazo > 0 )
    { 
        forma_pago = 'credito';
        $('#fecha_vencimiento').removeAttr( 'readonly' );
        $('#mostrar_medios_recaudos').attr('style','display:none');
    }
    $('#forma_pago').val( forma_pago );

    // Para llenar la fecha de vencimiento
    var fecha = new Date( $('#fecha').val() );
    
    $('#fecha_vencimiento').val( actualizar_fecha_vencimiento( fecha) );
    
    //Hacemos desaparecer el resto de sugerencias
    $('#div_list_suggestions').html('');
    $('#div_list_suggestions').hide();

    reset_tabla_ingreso();

    consultar_entradas_pendientes();

    $('#doc_proveedor_prefijo').focus();
}

// Recibe objeto Date()
function actualizar_fecha_vencimiento( fecha )
{
    if ( $('#forma_pago').val() == 'contado' )
    {
        dias_plazo = 0;
    }else{
        dias_plazo = dias_plazo_original;
    }

    fecha.setDate( fecha.getDate() + (dias_plazo + 1) );
    
    var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
    var dia = fecha.getDate();// Se le suma 1,

    if( mes < 10 )
    {
        mes = '0' + mes;
    }

    if( dia < 10 )
    {
        dia = '0' + dia;
    }

    return fecha.getFullYear() + '-' +  mes + '-' + dia;
}

function seleccionar_producto(item_sugerencia)
{
    reset_linea_ingreso_default();
    var fila = $('#linea_ingreso_default');

    // Asignar ID del producto al campo oculto
    inv_producto_id = item_sugerencia.attr('data-producto_id');

    // Asignar ID del motivo al campo oculto
    var mov = $('#inv_motivo_id').val().split('-');
    fila.find('.inv_motivo_id').html( mov[0] );

    // Asignar descripción del producto al TextInput
    $('#inv_producto_id').val( item_sugerencia.html() );
    //Hacemos desaparecer el resto de sugerencias
    $('#suggestions').html('');
    $('#suggestions').hide();
}

// Asignar valores de existecia_actual y costo_unitario
function consultar_existencia(bodega_id, producto_id)
{
    $('#div_cargando').show();
    var url = '../compras_consultar_existencia_producto';

    $.get( url, { transaccion_id: $('#core_tipo_transaccion_id').val(), liquida_impuestos: $('#liquida_impuestos').val(), bodega_id: bodega_id, producto_id: producto_id, fecha: $('#fecha').val(), proveedor_id: $('#proveedor_id').val() } )
        .done(function( respuesta ) {

            $('#div_cargando').hide();
        
            // Se valida la existencia actual
            $('#existencia_actual').val(respuesta.existencia_actual);
            $('#saldo_original').val( respuesta.existencia_actual );
            $('#tipo_producto').val(respuesta.tipo);

            $('#existencia_actual').attr('style','background-color:#97D897;'); // color verde

            if (respuesta.existencia_actual<=0)
            {
                $('#existencia_actual').attr('style','background-color:#FF8C8C;'); // color rojo
                
                var mov = $('#inv_motivo_id').val().split('-');
                // Si no es un motivo de entrada, no se permite seguir con existencia 0
                if ( mov[1] != 'entrada' && respuesta.tipo != 'servicio' ) 
                {	
                    $('#inv_producto_id').select();
                    return false;
                }
            }

            costo_unitario = respuesta.precio_compra;
            precio_unitario = respuesta.precio_compra;
            tasa_impuesto = respuesta.tasa_impuesto;

            if ( tasa_impuesto > 0 )
            {
                costo_unitario = respuesta.base_impuesto;
            }

            // Asignar datos a los controles (formateados visualmente para el usuario)
            //var precio_compra = 
            $('#precio_unitario').val( respuesta.precio_compra  );
            $('#tasa_impuesto').val( respuesta.tasa_impuesto + '%' );

            // Se pasa a ingresar las cantidades
            $('#cantidad').removeAttr('disabled');
            $('#cantidad').attr('style','background-color:white;');
            $('#cantidad').select();

            return true;
        });
}

/*
    validar_existencia_actual
*/
function validar_existencia_actual()
{
    // Si es una factura de compras, documento equivalente o documento soporte en adquisiciones, no se valida la existencia
    if( $('#url_id_transaccion').val() == 25 || $('#url_id_transaccion').val() == 29 || $('#url_id_transaccion').val() == 48 ) 
    { 
        return true;
    }

    // Se valida para las notas crédito directas (salidas de inventarios)
    if ( $('#tipo_producto').val() == 'servicio') { return true; }

    if ( parseFloat( $('#existencia_actual').val() ) < 0 ) 
    {
        alert('Saldo negativo a la fecha.');
        $('#cantidad').val('');
        $('#cantidad').select();
        return false;
    }/**/

    return true;
}


var numero_linea = 1;
function agregar_nueva_linea()
{
    if ( !calcular_precio_total() )
    {
        return false;
    }

    if ( !validar_existencia_actual() )
    {
        return false;
    }

    if( $('#url_id_transaccion').val() == 40 ) 
    { 
        // Si es una Nota Crédito Directa (salida de invetario)
        validacion_saldo_movimientos_posteriores();
    }else{
        agregar_la_linea()
    }

    
}

function agregar_la_linea()
{
    // Se escogen los campos de la fila ingresada
    var fila = $('#linea_ingreso_default');

    // agregar nueva fila a la tabla
    $('#ingreso_registros').find('tbody:last').append('<tr class="linea_registro" data-numero_linea="'+numero_linea+'">' + generar_string_celdas( fila ) + '</tr>');
    
    // Se calculan los totales
    calcular_totales();
    aplicar_retefuente( false );

    hay_productos++;
    $('#numero_lineas').text(hay_productos);
    set_cantidades_ingresadas();

    // Bajar el Scroll hasta el final de la página
    $("html, body").animate( { scrollTop: $(document).height()+"px"} );

    reset_linea_ingreso_default();

    numero_linea++;
}

// Crea la cadena de la celdas que se agregarán a la línea de ingreso de productos
// Debe ser complatible con las columnas de la tabla de ingreso de registros
function generar_string_celdas( fila )
{
    var celdas = [];
    var num_celda = 0;
    
    celdas[ num_celda ] = '<td style="display: none;"><div class="inv_motivo_id">'+ fila.find('.inv_motivo_id').html() +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="inv_bodega_id">'+ $('#inv_bodega_id').val() +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="inv_producto_id">'+ inv_producto_id +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="costo_unitario">'+ costo_unitario +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="precio_unitario">'+ precio_unitario +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="base_impuesto">'+ base_impuesto_unitario * cantidad +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_impuesto">'+ tasa_impuesto +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="valor_impuesto">'+ valor_impuesto_unitario * cantidad +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="cantidad">'+ cantidad +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="costo_total">'+ costo_unitario * cantidad +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="precio_total">'+ precio_total +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="tasa_descuento">'+ tasa_descuento +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td style="display: none;"><div class="valor_total_descuento">'+ valor_total_descuento +'</div></td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td> &nbsp; </td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td> <span style="background-color:#F7B2A3;">'+ inv_producto_id + "</span> " + $('#inv_producto_id').val() + '</td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td>'+ $('#inv_motivo_id option:selected').text() + '</td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td> '+ $('#existencia_actual').val() + '</td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td>'+ cantidad + ' </td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_unitario ) + '</td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td>'+ tasa_descuento + '% </td>';
    
    num_celda++;
    // ¿se va  amostrar valor del descuento?
    celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( valor_total_descuento.toFixed(2) ) + '</td>';
    
    num_celda++;

    celdas[ num_celda ] = '<td>'+ $('#tasa_impuesto').val() + '</td>';
    
    num_celda++;

    var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-btn fa-trash'></i></button>";
    celdas[ num_celda ] = '<td> '+ '$ ' + new Intl.NumberFormat("de-DE").format( precio_total.toFixed(2) ) + ' </td><td>' + btn_borrar + '</td>';

    var cantidad_celdas = celdas.length;
    var string_celdas = '';
    for (var i = 0; i < cantidad_celdas; i++)
    {
        string_celdas = string_celdas + celdas[i];
    }

    return string_celdas;
}



function cambiar_action_form( nueva_accion )
{
    var accion = $('#form_create').attr('action');
    var n = accion.search('compras');

    if( n === -1 )
    {
        // No está la palabra compras
        n = accion.search('factura_entrada_pendiente');
        $('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
    }else{
        $('#form_create').attr('action', accion.substr(0,n) + nueva_accion );
    }
}


// Para las notas crédito directas (salida de inventario)
function calcular_nuevo_saldo_a_la_fecha()
{
    // 0 es la cantidad_original
    var nuevo_saldo = parseFloat( $('#saldo_original').val() ) + 0 - parseFloat( $('#cantidad').val() );

    $('#existencia_actual').val( nuevo_saldo );
}

function validacion_saldo_movimientos_posteriores( )
{

    // Se escogen los campos de la fila ingresada
    var fila = $('#linea_ingreso_default');

    var url = '../inv_validacion_saldo_movimientos_posteriores/' + $('#inv_bodega_id').val() + '/' + inv_producto_id + '/' + $('#fecha').val() + '/' + cantidad + '/' + $('#existencia_actual').val() + '/salida';

    $.get( url )
        .done( function( data ) {
            if ( data != 0 )
            {
                $('#popup_alerta_danger').show();
                $('#popup_alerta_danger').text( data );
            }else{
                $('#popup_alerta_danger').hide();
                agregar_la_linea();
            }
        });
}



function consultar_entradas_pendientes()
{
    $('#div_entradas_pendientes').hide();
    $('#listado_entradas_seleccionadas').hide();
    $('#tabla_registros_documento').find('tbody').html( '' );
    url = '../compras_consultar_entradas_pendientes';
    $.get( url, { core_tercero_id: $('#core_tercero_id').val() } )
        .done(function( data ) {
            if ( data != 'sin_registros')
            {
                $('#div_entradas_pendientes').show( 500 );
                $('#listado_entradas_pendientes').html( data );
                $('.td_boton').show();
                $('.btn_agregar_documento').show();
                $('#div_ingreso_registros').hide();
            }else{
                $('#div_ingreso_registros').show( 500 );
            }
            return false;
        });/**/
}


var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
function validar_requeridos()
{
    control_requeridos = true;
    $( "*[required]" ).each(function() {
        if ( $(this).val() == "" )
        {
            $(this).focus();
            alert( 'Este campo es requerido: ' + $(this).attr('name') );
            control_requeridos = false;
            return false;
        }
    });

    return control_requeridos;
}

function reset_campos_formulario()
{
    $('#proveedor_id').val( '' );
    $('#proveedor_input').css( 'background-color','#FF8C8C' );
    $('#vendedor_id').val( '' );
    $('#inv_bodega_id').val( '' );
    $('#forma_pago').val( 'contado' );
    $('#fecha_vencimiento').val( '' );
    $('#lista_precios_id').val( '' );
    $('#lista_descuentos_id').val( '' );
    $('#liquida_impuestos').val( '' );
}

function reset_tabla_ingreso()
{
    $('.linea_registro').each(function( ){
        $(this).remove();
    });

    // reset totales
    $('#total_cantidad').text( '0' );

    // Subtotal (Sumatoria de base_impuestos por cantidad)
    $('#subtotal').text( '$ 0' );

    $('#descuento').text( '$ 0' );

    // Total impuestos (Sumatoria de valor_impuesto por cantidad)
    $('#total_impuestos').text( '$ 0' );

    // Total factura  (Sumatoria de precio_total)
    $('#total_factura').text( '$ 0' );


    reset_linea_ingreso_default()
}


function reset_linea_ingreso_default()
{
    $('#linea_ingreso_default input[type="text"]').val('0');
    $('#linea_ingreso_default input[type="text"]').attr('style','background-color:#ECECE5;');
    $('#linea_ingreso_default input[type="text"]').attr('disabled','disabled');


    $('#inv_motivo_id').attr('style','background-color:#ECECE5;');
    $('#inv_motivo_id').attr('disabled','disabled');

    $('#precio_unitario').removeAttr('style');
    $('#precio_unitario').removeAttr('disabled');

    $('#tasa_descuento').removeAttr('style');
    $('#tasa_descuento').removeAttr('disabled');

    $('#valor_total_descuento').removeAttr('style');
    $('#valor_total_descuento').removeAttr('disabled');

    $('#valor_unitario_descuento').removeAttr('style');
    $('#valor_unitario_descuento').removeAttr('disabled');

    $('#inv_producto_id').val('');
    $('#inv_producto_id').removeAttr('style');
    $('#inv_producto_id').removeAttr('disabled');
    $('#inv_producto_id').select();
    $("[data-toggle='tooltip']").tooltip('show');

    producto_id = 0; precio_total = 0; costo_total = 0; base_impuesto_total = 0; valor_impuesto_total = 0; tasa_impuesto = 0; tasa_descuento = 0; valor_total_descuento = 0; cantidad = 0; costo_unitario = 0; precio_unitario = 0; base_impuesto_unitario = 0; valor_impuesto_unitario = 0; valor_unitario_descuento = 0;
}

function calcular_precio_total()
{

	if (cantidad <= 0 || cantidad == undefined) {
        Swal.fire({
			icon: "error",
			title: "Alerta!",
			text: 'No ha ingresado Cantidad.',
		});

		return false;
	}

	if ( precio_unitario == 0 ) {
        Swal.fire({
			icon: "error",
			title: "Alerta!",
			text: 'Precio Unitario no puede ser cero (0).',
		});

        $('#precio_unitario').select();

		return false;
	}

	// Descuento del 100%
	if (precio_unitario == valor_unitario_descuento) {
		precio_total = 0;
		$("#precio_total").val(precio_total);
		$("#popup_alerta_danger").hide();
		return true;
	}
    
    precio_total = (precio_unitario - valor_unitario_descuento) * cantidad;
    
    $('#precio_total').val(0);

    if( $.isNumeric( precio_total ) && precio_total > 0 )
    {
        $('#precio_total').val( precio_total );
        return true;
    }else{
        precio_total = 0;
        return false;
    }
}


function validar_todo()
{
    if ( validar_documento_proveedor() ) { return false; }
    
    if ( !validar_requeridos() ) { return false; }

    if( hay_productos == 0) 
    {
        alert('No ha ingresado productos.');
        reset_linea_ingreso_default();
        return false;		  			
    }

    return true;
}