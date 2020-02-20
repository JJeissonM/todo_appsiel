@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
	
		<div class="marco_formulario">
	    <h4>Ingreso de observaciones de boletines</h4>
	    <hr>

		{{Form::open(array( 'route'=>array('calificaciones.observaciones_boletin.store'),'method'=>'POST', 'class'=>'form-horizontal', 'id' => 'form_create' ))}}
			<div class="row">
				<div class="col-sm-12">
					<b>Año:</b> <code> {{ $anio }}</code>
					<b>Periodo:</b>	<code> {{ $nom_periodo }}</code>
					<b>Curso:</b> <code> {{ $nom_curso }}</code>
					
					<input name="id_periodo" id="id_periodo" type="hidden" value="{{ $id_periodo }}"/>
					<input name="curso_id" id="curso_id" type="hidden" value="{{ $curso_id }}"/>
					<input name="anio" id="anio" type="hidden" value="{{ $anio }}"/>
				</div>
			</div>
			
			<table class="table table-responsive" id="tabla">
				<thead>
					<tr>
						<th>Estudiante</th>
						<th>Observación</th>
					</tr>
				</thead>
				<tbody>
					<?php $j=1; ?>
					@foreach($estudiantes as $campo)
					<?php 
						$estudiante = "estudiante".$j; 
						$observacion="observacion".$j;
					?>
					<tr> 
						<td>
							<b> {{ $campo->nombre_completo }}</b>
							<input name="estudiante[]" id="{{ $estudiante }}" type="hidden" value="{{ $campo->id_estudiante }}">
						</td>
						<td>
							<textarea name="observacion[]" id="{{ $observacion }}" class="form-control" rows="2"></textarea>
						</td>
					</tr>
						{{ Form::hidden('codigo_matricula[]',$campo->codigo) }}
					<?php $j++; ?>
					@endforeach
				</tbody>
			</table>
			{{ Form::hidden('cantidad_estudiantes',$j-1) }}

			@if( !is_null($estudiantes) )
				<div style="text-align: center; width: 100%;">
					{{ Form::bsButtonsForm( 'calificaciones/observaciones_boletin/create?id='.Input::get('id') ) }}
				</div>
			@else
				<div class='alert alert-warning'>
					<strong>Atención!</strong> <br/> No hay estudiantes matriculados en este curso.
				</div>
			@endif

			{{ Form::hidden('id_app',Input::get('id')) }}
		{{ Form::close() }}
			
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