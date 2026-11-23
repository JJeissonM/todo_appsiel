@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<h4>Nuevo registro</h4>
		<hr>
		{{ Form::open(['url'=>$form_create['url'],'id'=>'form_create']) }}

		<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

		<div class="alert alert-warning" id="div_documento_descuadrado" style="display: none;">
			<strong>¡Advertencia!</strong> Documento está descuadrado.
		</div>

		<div class="alert alert-danger" id="div_error_guardado_recaudo" style="display: none;">
			<button type="button" class="close" aria-label="Cerrar" onclick="$('#div_error_guardado_recaudo').hide();">
				<span aria-hidden="true">&times;</span>
			</button>
			<strong>Error:</strong> <span id="mensaje_error_guardado_recaudo"></span>
		</div>

		{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

		{{ Form::hidden( 'url_id', Input::get( 'id' ) ) }}
		{{ Form::hidden( 'url_id_modelo', Input::get( 'id_modelo' ) ) }}
		{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

		{{ Form::hidden( 'tipo_recaudo_aux', '', [ 'id' => 'tipo_recaudo_aux' ] ) }}

		<input type="hidden" name="lineas_registros" id="lineas_registros" value="{{ old('lineas_registros', '') }}">
		<input type="hidden" name="lineas_registros_retenciones" id="lineas_registros_retenciones" value="{{ old('lineas_registros_retenciones', '') }}">
		<input type="hidden" name="lineas_registros_descuento_pronto_pagos" id="lineas_registros_descuento_pronto_pagos"
			value="{{ old('lineas_registros_descuento_pronto_pagos', '') }}">
		<input type="hidden" name="lineas_registros_asientos_contables" id="lineas_registros_asientos_contables"
			value="{{ old('lineas_registros_asientos_contables', '') }}">
		<input type="hidden" name="lineas_registros_efectivo" id="lineas_registros_efectivo" value="{{ old('lineas_registros_efectivo', '') }}">
		<input type="hidden" name="lineas_registros_transferencia_consignacion"
			id="lineas_registros_transferencia_consignacion" value="{{ old('lineas_registros_transferencia_consignacion', '') }}">
		<input type="hidden" name="lineas_registros_tarjeta_debito" id="lineas_registros_tarjeta_debito" value="{{ old('lineas_registros_tarjeta_debito', '') }}">
		<input type="hidden" name="lineas_registros_tarjeta_credito" id="lineas_registros_tarjeta_credito" value="{{ old('lineas_registros_tarjeta_credito', '') }}">
		<input type="hidden" name="lineas_registros_cheques" id="lineas_registros_cheques" value="{{ old('lineas_registros_cheques', '') }}">

		{{ Form::close() }}

		<div class="marco_formulario">
			@include('tesoreria.incluir.tabla_resumen_operaciones_create')
		</div>

		<div class="marco_formulario">
			@include('tesoreria.recaudos_cxc.tabs_operaciones_recaudo')
		</div>

		<div class="marco_formulario">
			@include('tesoreria.incluir.tabs_medios_de_pago')
		</div>

	</div>
</div>

<br /><br />

@endsection

@section('scripts')

<script type="text/javascript">
	var hay_cheques = 0;
		var hay_efectivo = 0;
		var hay_retencion = 0;
		var hay_transferencia_consignacion = 0;
		var hay_tarjeta_debito = 0;
		var hay_tarjeta_credito = 0;
		var hay_asiento_contable = 0;
		var hay_descuento_pronto_pago = 0;
		
		$(document).ready(function(){
			
			// No reemplazar una fecha restaurada por Laravel después de un error.
			if ($('#fecha').val() == '') {
				asignar_fecha_hoy();
			}

			// También controla envíos con Enter u otros disparadores del formulario.
			$('#form_create').on('submit', function(event) {
				event.preventDefault();
				$('#btn_guardar').trigger('click');
			});

			$('#cliente_input').focus();

			$('#cliente_input').on('focus',function(){
		    	$(this).select();
		    });

			$("#cliente_input").after('<div id="clientes_suggestions"> </div>');

			// Al ingresar código, descripción o código de barras del producto
		    $('#cliente_input').on('keyup',function(event){

		    	var x = event.which || event.keyCode; // Capturar la tecla presionada

				if( x === 27 ) // 27 = ESC
				{
					$('#clientes_suggestions').html('');
		        	$('#clientes_suggestions').hide();
		        	return false;
				}

				/*
					Al presionar las teclas "flecha hacia abajo" o "flecha hacia arriba"
			    */
				if ( x === 40) // Flecha hacia abajo
				{
					var item_activo = $("a.list-group-item.active");					
					item_activo.next().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.next().html() );
					return false;

				}
				
				if ( x === 38) // Flecha hacia arriba
				{
					$(".flecha_mover:focus").prev().focus();
					var item_activo = $("a.list-group-item.active");					
					item_activo.prev().attr('class','list-group-item list-group-item-cliente active');
					item_activo.attr('class','list-group-item list-group-item-cliente');
					$('#cliente_input').val( item_activo.prev().html() );
					return false;
				}

				// Al presionar Enter
				if( x === 13 )
				{
					var item = $('a.list-group-item.active');
					
					if( item.attr('data-tercero_id') === undefined )
					{
						alert('El tercero ingresado no existe.');
		            	return false;
					}else{
						seleccionar_cliente( item );
		            	return false;
					}
				}

				var campo_busqueda = 'descripcion';
				if( $.isNumeric( $(this).val() ) ){
		    		var campo_busqueda = 'numero_identificacion';
		    	}

		    	// Si la longitud es menor a dos, todavía no busca
			    if ( $(this).val().length < 2 ) { return false; }

		    	var url = "{{ url('core_consultar_terceros') }}";

				$.get( url, { texto_busqueda: $(this).val(), campo_busqueda: campo_busqueda, filtrar_por_cliente: 1 } )
					.done(function( data ) {
						// Se llena el DIV con las sugerencias que arroja la consulta
		                $('#clientes_suggestions').show().html(data);
		                $('a.list-group-item.active').focus();
					});
		    });

		    //Al hacer click en alguna de las sugerencias (escoger un producto)
		    $(document).on('click','.list-group-item-autocompletar', function(){
		    	seleccionar_cliente( $(this) );
		    	return false;
		    });

		    $(document).on('click','#btn_mostrar_resumen_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_resumen_operaciones').show();
		    	$('#div_resumen_operaciones').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_resumen_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_resumen_operaciones').show();
		    	$('#div_resumen_operaciones').fadeOut(500);
		    });

		    $(document).on('click','#btn_mostrar_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_operaciones').show();
		    	$('#div_operaciones').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_operaciones', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_operaciones').show();
		    	$('#div_operaciones').fadeOut(500);
		    });

		    $(document).on('click','#btn_mostrar_medios_pago', function(){
		    	$(this).hide();
		    	$('#btn_ocultar_medios_pago').show();
		    	$('#div_medios_pago').fadeIn(500);
		    });

		    $(document).on('click','#btn_ocultar_medios_pago', function(){
		    	$(this).hide();
		    	$('#btn_mostrar_medios_pago').show();
		    	$('#div_medios_pago').fadeOut(500);
		    });

			// GUARDAR 
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}		

				var total_valor = parseFloat( $('#total_valor').text().substring(1) );

				if ( total_valor <= 0 )
				{
					alert('No ha seleccionado documentos a pagar.');
					return false;
				}
  
				// Los registros de las tablas son dinámicos. El envío por AJAX permite
				// mostrar cualquier excepción sin recargar ni perder lo ingresado.
				habilitar_campos_form_create();
				guardar_recaudo_cxc();
			});

			function guardar_recaudo_cxc()
			{
				var boton_guardar = $('#btn_guardar');
				boton_guardar.prop('disabled', true);
				$('#div_error_guardado_recaudo').hide();

				$.ajax({
					url: $('#form_create').attr('action'),
					type: 'POST',
					data: $('#form_create').serialize(),
					dataType: 'json'
				}).done(function(respuesta) {
					if (respuesta.redirect) {
						window.location.href = respuesta.redirect;
						return;
					}

					mostrar_error_guardado('El recaudo fue procesado, pero no se recibió la URL del documento.');
				}).fail(function(xhr) {
					var mensaje = 'Ocurrió un error al registrar el recaudo.';

					if (xhr.responseJSON && xhr.responseJSON.mensaje) {
						mensaje = xhr.responseJSON.mensaje;
					}

					mostrar_error_guardado(mensaje);
				}).always(function() {
					boton_guardar.prop('disabled', false);
				});
			}

			function mostrar_error_guardado(mensaje)
			{
				$('#mensaje_error_guardado_recaudo').text(mensaje);
				$('#div_error_guardado_recaudo').show();
				$('html, body').animate({
					scrollTop: $('#div_error_guardado_recaudo').offset().top - 20
				}, 300);
			}

		    function seleccionar_cliente(item_sugerencia)
		    {
				// Asignar descripción al TextInput
		        $('#cliente_input').val( item_sugerencia.html() );
		        $('#cliente_input').css( 'background-color','white ' );

		        // Asignar Campos ocultos
		        $('#cliente_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#referencia_tercero_id').val( item_sugerencia.attr('data-tercero_id') );
		        $('#core_tercero_id').val( item_sugerencia.attr('data-tercero_id') );

		        //Hacemos desaparecer el resto de sugerencias
		        $('#clientes_suggestions').html('');
		        $('#clientes_suggestions').hide();
		        
		        $('#descripcion').focus();
		        //get_documentos_pendientes_cxc( item_sugerencia.attr('data-tercero_id') );

		        return false;
		    }

			function habilitar_text($control){
				$control.removeAttr('disabled');
				$control.attr('style','background-color:white;');
			}

			function deshabilitar_text($control){
				$control.attr('style','background-color:#ECECE5;');
				$control.attr('disabled','disabled');
			}

			function deshabilitar_campos_form_create()
			{

				$('#fecha').attr('disabled','disabled');

				$('.custom-combobox').hide();

				$('#core_tercero_id').show();
				$('#core_tercero_id').attr('disabled','disabled');
				
			}

			function habilitar_campos_form_create()
			{
				$('#fecha').removeAttr('disabled');
				
				//$('.custom-combobox').show();

				//$('#core_tercero_id').hide();
				$('#core_tercero_id').removeAttr('disabled');
			}

			function asignar_fecha_hoy()
			{
				var today = new Date();
				var dd = today.getDate();
				var mm = today.getMonth()+1; //January is 0!
				var yyyy = today.getFullYear();

				if(dd<10) {
				    dd = '0'+dd
				} 

				if(mm<10) {
				    mm = '0'+mm
				} 

				today = yyyy + '-' + mm + '-' + dd;

				$('#fecha').val( today );
			}

			$.fn.actualizar_total_resumen_medios_pagos = function ( valor_linea )
			{
			    // Total resumen
			    var actual_valor_resumen_medios_pagos = parseFloat( $('#input_valor_total_resumen_medios_pagos').val() );
				var nuevo_valor_resumen_medios_pagos = actual_valor_resumen_medios_pagos + valor_linea;
			    $('#valor_total_resumen_medios_pagos').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_resumen_medios_pagos.toFixed(2) ) );
				$('#input_valor_total_resumen_medios_pagos').val( nuevo_valor_resumen_medios_pagos );
				//Math.round
				var valor_diferencia = ( parseFloat( $('#input_valor_total_resumen_medios_pagos').val() ) - parseFloat( $('#input_valor_total_resumen_operaciones').val() ) );
				$('#valor_diferencia').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_diferencia.toFixed(2) ) ); // .toFixed(2)
				$('#input_valor_diferencia').val( valor_diferencia );

				$.fn.validar_diferencia( valor_diferencia );
			};

			$.fn.validar_diferencia = function( valor_diferencia )
			{
				if ( valor_diferencia.toFixed(2) == 0 )
				{
					$('#btn_guardar').show(); // ok
					$('#div_documento_descuadrado').hide();
					$('#valor_diferencia').removeAttr('style');
				}else{
					$('#btn_guardar').hide();
					$('#div_documento_descuadrado').show();
					$('#valor_diferencia').attr('style','background-color: #ffa3a3;');
				}
			}

			$.fn.actualizar_total_resumen_operaciones = function ( valor_linea )
			{
			    // Total resumen
			    var actual_valor_resumen_operaciones = parseFloat( $('#input_valor_total_resumen_operaciones').val() );
				var nuevo_valor_resumen_operaciones = actual_valor_resumen_operaciones + valor_linea;
			    $('#valor_total_resumen_operaciones').text( '$ ' + new Intl.NumberFormat("de-DE").format( nuevo_valor_resumen_operaciones.toFixed(2) ) );
				$('#input_valor_total_resumen_operaciones').val( nuevo_valor_resumen_operaciones );

				var valor_diferencia = parseFloat( $('#input_valor_total_resumen_medios_pagos').val() ) - parseFloat( $('#input_valor_total_resumen_operaciones').val() );
				$('#valor_diferencia').text( '$ ' + new Intl.NumberFormat("de-DE").format( valor_diferencia.toFixed(2) ) );
				$('#input_valor_diferencia').val( valor_diferencia );

				$.fn.validar_diferencia( valor_diferencia );
			};
		});


</script>
@endsection
