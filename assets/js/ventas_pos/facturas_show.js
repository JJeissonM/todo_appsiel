// AL CARGAR EL DOCUMENTO
$(document).ready(function () {

	// Elementos al final de la página
	$('#cliente_input').parent().parent().attr('style','left: 0');
	$('#cliente_input').parent().parent().attr('class', 'elemento_fondo');

	$('#cliente_input').on('focus', function () {
		$(this).select();
	});

	$("#cliente_input").after('<div id="clientes_suggestions"> hello </div>');

	// Al ingresar código, descripción o código de barras del producto
	$(document).on('keyup','#cliente_input', function (event) {

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

				if ($(this).val() == '') {
					return false;
				}

				var item = $('a.list-group-item.active');

				if (item.attr('data-cliente_id') === undefined)
				{
					alert('El cliente ingresado no existe.');
					reset_campos_formulario();
				} else {
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

                
        console.log('go2');

				$.get(url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda })
					.done(function (data) {
                        
        console.log('go3');
						// Se llena el DIV con las sugerencias que arooja la consulta
						$('#clientes_suggestions').show().html(data);
						$('a.list-group-item.active').focus();
					});
				break;
		}

        function seleccionar_cliente(item_sugerencia) {        
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
    
            reset_tabla_ingreso_items();
            reset_resumen_de_totales();
            reset_linea_ingreso_default();
    
            $.get( url_raiz + '/vtas_get_lista_precios_cliente' + "/" + $('#cliente_id').val())
                .done(function (data) {
                    precios = data[0];
                    descuentos = data[1];
                });
    
            if ( !$.isNumeric( parseInt( $('#core_tercero_id').val() ) ) ) {
                Swal.fire({
                    icon: 'error',
                    title: 'Alerta!',
                    text: 'Error al seleccionar el cliente. Ingrese un cliente correcto.'
                });
            }
    
            // Bajar el Scroll hasta el final de la página
            //$("html, body").animate({scrollTop: $(document).height() + "px"});
        }
	});	
});