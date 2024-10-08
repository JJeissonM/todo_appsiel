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

/**
 * Funcionalidad pendente: Cambiar Mesa
 */
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

/**
 * 
 */
function reset_componente_mesas()
{
    $('.btn_mesa').removeAttr('disabled');
    $('.btn_mesa').attr('class','btn btn-default btn_mesa');
    $('#lbl_mesa_seleccionada').text( '' );
}

$(document).ready(function () {

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

});