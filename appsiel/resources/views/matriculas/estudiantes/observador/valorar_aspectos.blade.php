@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Actualización</h4>
		    <hr>

			{{Form::open( ['url' => '/matriculas/estudiantes/observador/valorar_aspectos','method'=>'post', 'class'=>'form-horizontal', 'id' => 'form_create'] ) }}

				<div class="form-group">
					<div class="alert alert-info">
					  <strong>Convenciones!</strong> <br/> 
					  S= Siempre &nbsp;&nbsp;&nbsp;&nbsp;   CS= Casi siempre  &nbsp;&nbsp;&nbsp;&nbsp;      AV= Algunas veces  &nbsp;&nbsp;&nbsp;&nbsp; N= Nunca
					</div>
				</div>

				{{ Form::bsButtonsForm( 'web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

				{{ Form::bsTextArea('observacion_general', $observacion_general, 'Observación general', []) }}

				@include('matriculas.estudiantes.observador.valorar_aspectos_tabla_formulario')
				
				<br/>

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

			{{Form::close()}}
		</div>
	</div>	
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