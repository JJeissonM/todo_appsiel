var hay_error_password = true;

function reset_datos_mesa()
{
    $('#div_pedidos_mesero_para_una_mesa').html('');
    $('#lbl_mesa_seleccionada').html('');
    //$('.btn_mesa').removeAttr('disabled');
    $('.btn_mesa').attr('class','btn btn-default btn_mesa');

    $('.linea_registro').each(function () {
        $(this).remove();
    });
    
    hay_productos = 0;
    numero_lineas = 0;
    
    $('#ingreso_registros').find('tbody').html('');
    $('#numero_lineas').text('0');

    $('#accordionExample').find('button').each(function () {
        $(this).parent().show();
    });

    $("#div_ingreso_registros").find('h5').html('Ingreso de productos<br><span style="color:red;">NUEVO PEDIDO</span>');


    $('#btn_guardar_factura').show();
    $('#btn_modificar_pedido').hide();
    $('#btn_anular_pedido').hide();

     // reset totales
     $('#total_cantidad').text('0');

     // Total factura  (Sumatoria de precio_total)
     $('#total_factura').text('$ 0');
     $('#valor_total_factura').val(0);
}

function validate_password()
{
    var password = 'a3p0';
    if ( $('#seller_password').val() != '') {
        password = $('#seller_password').val();
    }
    var url = url_raiz + "/" + "core/validate_password" + "/" + $('#vendedor_id').attr( 'data-user_id') + "/" + password;

    $.get(url, function (respuesta) {
        
        document.getElementById('btn_validate_password').children[0].className = 'fa fa-check';
        
        //$("#").children('.fa fa-spinner fa-spin').attr('class','fa-check');
        if (respuesta == 'ok') {
            $('#lbl_vendedor_mesero').text( $('#lbl_vendedor_modal').text() );
            $("#modal_password").modal("hide");
            hay_error_password = false;
        }else{
            $('#lbl_error_password').show();
            hay_error_password = true;
        }
    });
}

function activar_mesas_disponibles_mesero()
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
            if ( arr_disponibles.includes( parseInt($(this).attr('data-mesa_id') ) ) ) {
                $(this).removeAttr('disabled');
            }			
        });
    });
}

function reset_componente_meseros()
{
    $('.vendedor_activo').attr('class','btn btn-default btn_vendedor');
    $('#lbl_vendedor_mesero').text( '' );
}

$(document).ready(function () {
	
    $('.btn_vendedor').on('click', function (e) {
		e.preventDefault();
		$('.btn_mesa').attr('disabled','disabled');

        $("#modal_password").modal({backdrop: "static"});

		$('.vendedor_activo').attr('class','btn btn-default btn_vendedor');

		$(this).attr('class','btn btn-default btn_vendedor vendedor_activo');

		$('#vendedor_id').val( $(this).attr('data-vendedor_id') );
		$('#vendedor_id').attr( 'data-vendedor_descripcion', $(this).attr('data-vendedor_descripcion') );
		$('#vendedor_id').attr( 'data-user_id', $(this).attr('data-user_id') );

		$('#lbl_vendedor_mesero').text( '' );
        hay_error_password = true;
		
		reset_datos_mesa();
		
	});

    $(document).on('click', '.btn_numero_teclado', function () {
        var seller_password = $('#seller_password').val();
        $('#seller_password').val( seller_password + $(this).text() );
    });

    $(document).on('click', '#btn_clear_teclado', function () {
        $('#seller_password').val('');
        $('#lbl_error_password').hide();
    });
    
    $("#modal_password").on('shown.bs.modal', function(){
        $('#lbl_vendedor_modal').text( $('#vendedor_id').attr( 'data-vendedor_descripcion') );
        $('#lbl_error_password').hide();
        $('#seller_password').val('');
        $('#seller_password').focus();
    });

    $("#seller_password").on('keyup', function(){
        var codigo_tecla_presionada = event.which || event.keyCode;
        if (codigo_tecla_presionada == 13 ) { // 13 = Tecla Enter
            validate_password();
        }else{
            $('#lbl_error_password').hide();
        }
    });

    $("#btn_validate_password").on('click', function(e){            
		e.preventDefault();
		$(this).children('.fa-check').attr('class','fa fa-spinner fa-spin');
        validate_password();
    });

    $("#modal_password").on('hidden.bs.modal', function(){
        if (hay_error_password) {
            $('.vendedor_activo').attr('class','btn btn-default btn_vendedor');
        }else{
			activar_mesas_disponibles_mesero();
		}
    });
});