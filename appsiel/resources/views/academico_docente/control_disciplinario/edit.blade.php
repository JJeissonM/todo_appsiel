@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
	    <h4>Ingreso de control disciplinario</h4>
	    <hr>

		{{Form::open(array('route'=>array('matriculas.control_disciplinario.update','editar1'),'method'=>'PUT','class'=>'form-horizontal','id'=>'form_create'))}}

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
					<?php 
						$cantidad = count($vec_estudiantes);
					?>
					@for($j = 0; $j < $cantidad ; $j++)
					<?php 
						$k = $j + 1;
						//$control_id = "control_id".$k;
						$estudiante = "estudiante".$k;
						$codigo_1_id = "codigo_1_id".$k;
						$codigo_2_id = "codigo_2_id".$k;
						$codigo_3_id = "codigo_3_id".$k;
						$observacion_adicional = "observacion_adicional".$k;
					?>
					<tr> 
						<td>
							
							{{ Form::hidden('control_id[]',$vec_estudiantes[$j]['control_id']) }}


							<b> {{ $vec_estudiantes[$j]['nombre_completo'] }} </b>
							<input name="estudiante[]" id="{{ $estudiante }}" type="hidden" value="{{ $vec_estudiantes[$j]['id_estudiante'] }}">
						</td>
						<td>
							{{ Form::select( 'codigo_1_id[]',$codigos, $vec_estudiantes[$j]['codigo_1_id'], [ 'class' => 'combobox'] ) }}
						</td>
						<td>
							{{ Form::select( 'codigo_2_id[]',$codigos, $vec_estudiantes[$j]['codigo_2_id'], [ 'class' => 'combobox'] ) }}
						</td>
						<td>
							{{ Form::select( 'codigo_3_id[]',$codigos, $vec_estudiantes[$j]['codigo_3_id'], [ 'class' => 'combobox'] ) }}
						</td>
						<td>
							<textarea name="observacion_adicional[]" id="{{ $observacion_adicional }}" class="form-control" rows="2">{{ $vec_estudiantes[$j]['observacion_adicional'] }}</textarea>
						</td>
					</tr>
					@endfor
				</tbody>
			</table>
			{{ Form::hidden('cantidad_estudiantes', $cantidad) }}

			@if( !is_null($vec_estudiantes) )
				<div style="text-align: center; width: 100%;">
					{{ Form::bsButtonsForm( url()->previous() ) }}
				</div>
			@else
				<div class='alert alert-warning'>
					<strong>Atención!</strong> <br/> No hay estudiantes matriculados en este curso.
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