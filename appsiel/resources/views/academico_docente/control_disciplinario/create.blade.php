@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
	    <h4>Ingreso de control disciplinario</h4>
	    <hr>

		{{Form::open(array('route'=>array('matriculas.control_disciplinario.store'),'method'=>'POST', 'class'=>'form-horizontal', 'id'=>'form_create'))}}
			<div class="row">
				<div class="col-sm-12">
					<b>Semana:</b> <code> {{ $semana->descripcion }}</code>
					<b>Curso:</b> <code> {{ $nom_curso }}</code>
					<b>Asignatura:</b>	<code> {{ $nom_asignatura }}</code>
					
					<input name="semana_id" id="semana_id" type="hidden" value="{{ $semana->id }}"/>
					<input name="curso_id" id="curso_id" type="hidden" value="{{ $curso_id }}"/>
					<input name="asignatura_id" id="asignatura_id" type="hidden" value="{{ $asignatura_id }}"/>
				</div>
			</div>
			
			<table class="table table-responsive" id="tabla">
				<thead>
					<tr>
						<th>Estudiante</th>
						<th>Código disciplinario #1</th>
						<th>Código disciplinario #2</th>
						<th>Código disciplinario #3</th>
						<th>Observación adicional</th>
					</tr>
				</thead>
				<tbody>
					<?php $j=1; ?>
					@foreach($estudiantes as $campo)
					<?php 
						$estudiante = "estudiante".$j;
						$codigo_1_id = "codigo_1_id".$j;
						$codigo_2_id = "codigo_2_id".$j;
						$codigo_3_id = "codigo_3_id".$j;
						$observacion_adicional = "observacion_adicional".$j;
					?>
					<tr> 
						<td>
							<b> {{ $campo->nombre_completo }}</b>
							<input name="estudiante[]" id="{{ $estudiante }}" type="hidden" value="{{ $campo->id_estudiante }}">
						</td>
						<td>
							{{ Form::select( 'codigo_1_id[]',$codigos, null, [ 'class' => 'combobox' ] ) }}
						</td>
						<td>
							{{ Form::select( 'codigo_2_id[]',$codigos, null, [ 'class' => 'combobox'] ) }}
						</td>
						<td>
							{{ Form::select( 'codigo_3_id[]',$codigos, null, [ 'class' => 'combobox'] ) }}
						</td>
						<td>
							<textarea name="observacion_adicional[]" id="{{ $observacion_adicional }}" class="form-control" rows="2"></textarea>
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
					{{ Form::bsButtonsForm( url()->previous() ) }}
				</div>
			@else
				<div class='alert alert-warning'>
					<strong>Atención!</strong> <br/> No hay estudiantes matriculados en este curso.
					<a class="btn btn-warning btn-sm" href="{{ url()->previous() }}"><i class="fa fa-btn fa-arrow-left"></i> Volver</a>
				</div>
			@endif

			{{ Form::hidden('app_id',Input::get('id')) }}

			{{ Form::hidden('aux_curso_id', $aux_curso_id) }}

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