@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Actualización</h4>
		    <hr>

			{{Form::open(['url' => 'academico_docente/guardar_valoracion_aspectos','method'=>'post', 'id' => 'form_create', 'class'=>'form-horizontal']) }}

				<div class="form-group">
					<div class="alert alert-success">
					  <strong>Convenciones!</strong> <br/>
					  @include('academico_docente.estudiantes.lbl_convenciones_valorar_aspectos_observador')
					  
					</div>
				</div>

				{{ Form::bsTextArea('observacion_general', $observacion_general, 'Observación general', []) }}

				@include('matriculas.estudiantes.observador.valorar_aspectos_tabla_formulario')
				
				<br/>

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('curso_id',Input::get('curso_id'))}}
				{{ Form::hidden('asignatura_id',Input::get('asignatura_id'))}}

			    {{ Form::bsButtonsForm( 'academico_docente/revisar_estudiantes/curso_id/'.Input::get('curso_id').'/id_asignatura/'.Input::get('asignatura_id').'?id='.Input::get('id') ) }}
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