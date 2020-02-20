@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    {{ Form::open(['url'=>'creacion_masiva_registros_store','id'=>'form_create','files' => true]) }}
				<?php
					echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm( url()->previous() ).'</div>';
				?>

				{!! $tabla !!}

				{{ Form::hidden('url_id',Input::get('id')) }}
				{{ Form::hidden('url_id_modelo',$modelo_id) }}

				{{ Form::hidden( 'lista_registros',null,['id' => 'lista_registros'] ) }}
				
			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			// GUARDAR Cuena de cobro
			$('#bs_boton_guardar').click(function(event){
				event.preventDefault();

				// Se obtienen todos los datos del formulario y se envían
				// Se validan nuevamente los campos requeridos
				var control = 1;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" ) {
					  $(this).focus();
					  control = 0;
					  alert('Este campo es requerido.');
					  return false;
					}else{
					  control = 1;
					}
				});

				if (control==1) 
				{

					$( "select" ).each(function() {
						var texto = $("option:selected", this).text();
						var valor = $(this).val();

						$(this).parent("td").text( valor + "-" + texto );
						 
					});
					/**/

					$( "input[type=text]" ).each(function() {
						var texto2 = $(this).val();
						var valor2 = $(this).val();

						$(this).parent("td").text( valor2 + "-" + texto2 );
						 
					});

					// Se asigna la tabla de ingreso de registros a un campo hidden
					var tabla_registros = $('#tabla_registros').tableToJSON();

					$('#lista_registros').val( JSON.stringify(tabla_registros) );

					$('#form_create').submit();
				}else{
					alert('Faltan campos por llenar.');
				}
					
			});
		});
	</script>
@endsection