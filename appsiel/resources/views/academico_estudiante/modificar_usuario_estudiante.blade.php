@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	<?php 
		$variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
	?>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Modificando el registro</h4>
		    <hr>

		    {{ Form::model($registro, ['url' => 'academico_estudiante/actualizar_usuario_estudiante/'.$registro->id.$variables_url, 'method' => 'PUT' ] ) }}

				<div class="row" style="padding:5px;">
					{{ Form::bsText('name',null,'Nombre',[]) }}
				</div>

				<div class="row" style="padding:5px;">
					{{ Form::bsText('email',null,'Email',[]) }}
				</div>

				{{ Form::bsButtonsForm( 'academico_estudiante/usuarios_estudiantes'.$variables_url ) }}

				{{ Form::hidden('app_id',Input::get('id')) }}
				{{ Form::hidden('modelo_id',Input::get('id_modelo')) }}

			{{Form::close()}}
		
		</div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});

		});
	</script>
@endsection