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

				<input type="hidden" name="compras_doc_relacionado_id" id="compras_doc_relacionado_id" value="{{ $doc_encabezado->id }}" required="required">

				<input type="hidden" name="core_tercero_id" id="core_tercero_id" value="{{$doc_encabezado->core_tercero_id}}" >
				<input type="hidden" name="proveedor_id" id="proveedor_id" value="{{$doc_encabezado->proveedor_id}}" required="required">
				<input type="hidden" name="fecha_vencimiento" id="fecha_vencimiento" value="{{$doc_encabezado->fecha_vencimiento}}" required="required">

				<div id="popup_alerta"> </div>

				<br/>

			    {!! $tabla !!}
				
				<div style="text-align: right;">
	            	<table style="display: inline;">
	            		<tr>
	            			<td style="text-align: right; font-weight: bold;"> 
								Total: &nbsp; 
							</td>
							<td> 
								<div id="total_nota"> $ 0 </div> 
							</td>
	            		</tr>
	            	</table>
				</div>

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

			var continuar = true;
			var guardando = false;

			$('.cantidad_devolver').on('keyup',function(){

				var fila = $(this).closest('tr');

				if( !validar_input_numerico( $(this) ) && $(this).val() > 0 )
				{
					$('#btn_guardar').hide();
					return false;
				}else{
					//calcula_nuevo_saldo_a_la_fecha( fila );
					$('#btn_guardar').show();
				}

				var cantidad_linea = parseFloat( fila.find('.cantidad_linea').val() );
				if ( $(this).val() > cantidad_linea )
				{
					$(this).attr('style','background-color:#FF8C8C;');
					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color','red');
					$('#popup_alerta').text( 'La cantidad a devolver es mayor que la cantidad de la factura.' );
					$('#btn_guardar').hide();
				}else{
					$(this).attr('style','background-color:white;');
					$('#popup_alerta').hide();
					$('#btn_guardar').show();
                    $('#popup_alerta_danger').hide();
					validacion_saldo_movimientos_posteriores( fila );
					calcular_total_nota();
				}
			});

			$('.cantidad_devolver').on('blur',function(){
				var fila = $(this).closest('tr');
				validacion_saldo_movimientos_posteriores( fila );
				calcular_total_nota();
			});

			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();
				$('#form_create').trigger('submit');
			});

			$('#form_create').on('submit', function(event){
				event.preventDefault();

				if (guardando) {
					return false;
				}

				if ( !continuar )
				{
					alert('Hay errores por corregir.');
					return false;
				}

				if ( !validar_requeridos() )
				{
					return false;	
				}

				if ( !validar_cantidades_devolver() )
				{
					return false;	
				}

				guardando = true;
				$('#btn_guardar').hide();

				var formulario = $(this);
				$.ajax({
					url: formulario.attr('action'),
					type: 'POST',
					data: formulario.serialize(),
					dataType: 'json'
				}).done(function(respuesta) {
					window.location.href = respuesta.redirect;
				}).fail(function(xhr) {
					var mensaje = 'No fue posible guardar la nota crédito.';
					if (xhr.responseJSON && xhr.responseJSON.message) {
						mensaje = xhr.responseJSON.message;
					}

					$('#popup_alerta').show();
					$('#popup_alerta').css('background-color', 'red');
					$('#popup_alerta').text(mensaje);
					$('#btn_guardar').show();
					guardando = false;
				});

				return false;
			});

			function validar_cantidades_devolver()
			{
				control = true;
				var cantidad = 0;
				$('.cantidad_devolver').each(function()
				{
				    if ( $(this).val() != '' )
				    {
				    	cantidad += parseFloat( $(this).val() );
				    }
				});

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
            
            function validacion_saldo_movimientos_posteriores( fila )
            {
            	var cantidad_nueva = 0;//fila.find('.cantidad_anterior').val() - fila.find('.cantidad_devolver').val();
                var url = '../inv_validacion_saldo_movimientos_posteriores/' + fila.attr('data-bodega_id') + '/' + fila.attr('data-producto_id') + '/' + $('#fecha').val() + '/' + fila.find('.cantidad_devolver').val() + '/' + 'no' + '/salida' + '/' + 0;

                $.get( url )
                    .done( function( data ) {
                        if ( data != 0 )
                        {
                            $('#popup_alerta_danger').show();
                            $('#popup_alerta_danger').text( data );
							$('#btn_guardar').hide();
							fila.find('.cantidad_devolver').select()
							continuar = false;
                        }else{
							$('#btn_guardar').show();
                            $('#popup_alerta_danger').hide();
							continuar = true;
                        }
                    });
            }
            
            function calcular_total_nota()
            {
            	var total_nota = 0;

				$('.linea_registro').each(function () {

					var valor_linea, cantidad_devolver;

					valor_linea = $(this).find('.valor_linea').val() || 0;
					cantidad_devolver = $(this).find('.cantidad_devolver').val() || 0;

					console.log( valor_linea, cantidad_devolver );
					total_nota += parseFloat(valor_linea) * parseFloat(cantidad_devolver);
				});

				$('#total_nota').text( '$ ' + total_nota.toLocaleString("es-CO") );
			}

			/*function calcula_nuevo_saldo_a_la_fecha( fila )
			{
				// al ingresar cantidades a devolver son salidas de inventario que reducen el saldo a la fecha
				var nuevo_saldo = parseFloat( fila.attr('data-saldo_original') ) - parseFloat( fila.find('.cantidad_devolver').val() );

				fila.find('.saldo_a_la_fecha').val( nuevo_saldo );
			}*/
		});
	</script>
@endsection
