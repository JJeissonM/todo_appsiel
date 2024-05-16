@extends('layouts.principal')

@section('content')
	
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="col-sm-offset-2 col-sm-8">
		<div class="panel panel-success">
			<div class="panel-heading" align="center">
				<h4>Creación de un nuevo registro</h4>
			</div>
			
			<div class="panel-body">
				{{Form::open(array('route'=>array('calificaciones.asistencia_clases.store' ), 'class'=>'form-horizontal', 'id' => 'form_create' ) ) }}
					<h2 align="center">Registro de asistencias</h2>
					<h4>
						<b>Fecha: </b> {{ $fecha }}
						{{ Form::hidden('fecha',$fecha) }}
						<br/>
						<b>Curso: </b> {{ $curso->descripcion }}
						{{ Form::hidden('curso_id',$curso->id) }}
						<br/>
						<b>Asignatura: </b> {{ $asignatura->descripcion }}
						{{ Form::hidden('asignatura_id',$asignatura->id) }}
					</h4>
					<div class="row">
						<div class="col-sm-6 well">Estudiante</div>
						<div class="col-sm-3 well">¿Asistió?</div>
						<div class="col-sm-3 well">Anotación</div>
					</div>
					<div class="list-group">
						<?php $cantidad_estudiantes=0; ?>
						@foreach ($registros as $fila)

						<div class="row list-group-item" >
							<div class="col-sm-6">
								{{ Form::hidden('id_estudiante[]',$fila->id) }}  
								{{ $fila->nombre_completo }} 
							</div>
							<div class="col-sm-3">
								<div class="radio-inline">
								  <label>
								  	<input type="radio" name="asistio-{{$cantidad_estudiantes}}" value="Si" checked="checked">Si
								  </label>
								</div>
								<div class="radio-inline">
								  <label>
								  	<input type="radio" name="asistio-{{$cantidad_estudiantes}}" value="No">No
								  </label>
								</div>
							</div>
							<div class="col-sm-3">
								{{ Form::text('anotacion[]',null,['class'=>'form-control']) }}
							</div>
						</div>
							<?php $cantidad_estudiantes++; ?>
						@endforeach
					</div>

					{{ Form::hidden('cantidad_estudiantes',$cantidad_estudiantes) }}

					{{ Form::hidden('url_id',Input::get('id'))}}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}

				    {{ Form::bsButtonsForm('web?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

				{{Form::close()}}
			</div>
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