@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_1')
	<style>
		#suggestions {
		    position: absolute;
		    z-index: 9999;
		}
		#proveedores_suggestions {
		    position: absolute;
		    z-index: 9999;
		}

		#existencia_actual, #tasa_impuesto{
			width: 35px;
		}

		#popup_alerta{
			display: none;/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right:10px; /*A la izquierda deje un espacio de 0px*/
			bottom:10px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index:999999;
			float: right;
    		text-align: center;
    		padding: 5px;
    		opacity: 0.7;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">		    

			<h4>Nuevo registro</h4>
			<hr>
			{{ Form::open(['url'=> $form_create['url'],'id'=>'form_create']) }}
				<?php
				  if (count($form_create['campos'])>0) {
				  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm2($url).'</div>';
				  }else{
				  	echo "<p>El modelo no tiene campos asociados.</p>";
				  }
				?>

				{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo')) }}
				{{ Form::hidden('url_id_transaccion',Input::get('id_transaccion')) }}

				<input type="hidden" name="ventas_doc_relacionado_id" id="ventas_doc_relacionado_id" value="{{ $doc_encabezado->id }}" required="required">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$doc_encabezado->core_tercero_id}}" >
				<input type="hidden" name="cliente_id" id="cliente_id" value="{{$doc_encabezado->cliente_id}}" required="required">
				<input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="{{$doc_encabezado->fecha_vencimiento}}" required="required">

				<div id="popup_alerta"> </div>

				<br/>

			    {!! $tabla !!}

			{{ Form::close() }}
			
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">

		$(document).ready(function(){

			$('#fecha').val( get_fecha_hoy() );

			$('#fecha').focus( );

			$('.cantidad_devolver').on('keyup',function(event){
				event.preventDefault();

				var codigo_tecla_presionada = event.which || event.keyCode;

				if ( codigo_tecla_presionada == 13 ) // Enter
				{
					$('#btn_guardar').focus();
					return false;
				}
				
				if( !validar_input_numerico( $(this) ) )
				{
					$('#btn_guardar').hide();
					return false;
				}else{
					$('#btn_guardar').show();
				}

				validar_cantidad_devolver($(this));
			});

			$(document).on('click', '.btn_devolver_linea', function(event){
				event.preventDefault();

				asignar_cantidad_total($(this).closest('tr'));
			});

			$(document).on('click', '#btn_devolver_todo', function(event){
				event.preventDefault();

				$('#tabla_registros_nota_credito tbody tr').each(function(){
					asignar_cantidad_total($(this));
				});
			});

			function asignar_cantidad_total(fila)
			{
				var cantidad_linea = fila.find('.cantidad_linea').val();
				var input_cantidad = fila.find('.cantidad_devolver');

				input_cantidad.val(cantidad_linea);
				validar_cantidad_devolver(input_cantidad);
			}

			function validar_cantidad_devolver(input_cantidad)
			{
				var fila = input_cantidad.closest('tr');
				var cantidad_linea = parseFloat(fila.find('.cantidad_linea').val());
				var cantidad_devolver = parseFloat(input_cantidad.val());

				if (isNaN(cantidad_devolver)) {
					input_cantidad.attr('style','background-color:white;');
					$('#popup_alerta').hide();
					$('#btn_guardar').show();
					return true;
				}

				if (cantidad_devolver > cantidad_linea)
				{
					input_cantidad.attr('style','background-color:#FF8C8C;');
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'La cantidad a devolver es mayor que la cantidad de la factura.' );
					$('#btn_guardar').hide();
					return false;
				}

				input_cantidad.attr('style','background-color:white;');
				$('#popup_alerta').hide();
				$('#btn_guardar').show();

				return true;
			}



			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;	
				}

				if ( !validar_cantidades_devolver() )
				{
					return false;	
				}

				// Desactivar el click del botón
				$( this ).off( event );
				$('#fecha_vencimiento').val( $('#fecha').val() );

				// Enviar formulario
				$('#form_create').submit();
					
			});

			function validar_cantidades_devolver()
			{
				control = true;
				var cantidad = 0;
				$('.cantidad_devolver').each(function()
				{
					if ( $(this).val() != '' )
					{
						if (!validar_cantidad_devolver($(this))) {
							control = false;
							return false;
						}

						cantidad += parseFloat( $(this).val() );
					}
				});

				if (!control) {
					return control;
				}

				$('#popup_alerta').hide();

				if ( cantidad == 0 )
				{
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'No se han ingresado cantidades a devolver.' );
					control = false;
				}

				return control;
			}

			var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
			function validar_requeridos()
			{
				control_requeridos = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" )
					{
					 	$(this).focus();
						$('#popup_alerta').show();
						$('#popup_alerta').css('background-color','red');
						$('#popup_alerta').text( 'Este campo es requerido: ' + $(this).attr('name') );
						control_requeridos = false;
						return false;
					}else{
						$('#popup_alerta').hide();
					}
				});

				return control_requeridos;
			}
		});
	</script>
@endsection
