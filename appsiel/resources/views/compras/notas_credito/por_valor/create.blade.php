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

            $('#valor_total').on('keyup',function(){

                var fila = $(this).closest('tr');

                if( !validar_input_numerico( $(this) ) ) // && $(this).val() > 0
                {
                    $('#btn_guardar').hide();
                    return false;
                }else{
                    $('#btn_guardar').show();
                }

                var valor_saldo_pendiente = parseFloat( $('#valor_saldo_pendiente').val() );
                if ( $(this).val() > valor_saldo_pendiente )
                {
                    $(this).attr('style','background-color:#FF8C8C;');
                    $('#popup_alerta').show();
                    $('#popup_alerta').css('background-color','red');
                    $('#popup_alerta').text( 'El valor ingresado es mayor que el Valor Penediente de la factura.' );
                    $('#btn_guardar').hide();
                }else{
                    $(this).attr('style','background-color:white;');
                    $('#popup_alerta').hide();
                    $('#btn_guardar').show();
                    $('#popup_alerta_danger').hide();
                }
            });

			// GUARDAR EL FORMULARIO
			$('#btn_guardar').click(function(event){
				event.preventDefault();

				if ( !continuar )
				{
					alert('Hay errores por corregir.');
					return false;
				}

				if ( !validar_requeridos() )
				{
					return false;	
				}

				// Enviar formulario
				//console.log( 'Enviar formulario' );
				$('#form_create').submit();
					
			});

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