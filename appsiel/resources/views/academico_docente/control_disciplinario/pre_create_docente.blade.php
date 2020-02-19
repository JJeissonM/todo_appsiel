@extends('academico_docente.control_disciplinario.pre_create')

@section('campos_selects')
	<div class="row" style="padding:5px;">
		{{ Form::bsText('curso_id_no',$curso->descripcion,'Curso',['disabled'=>'disabled']) }}

		{{ Form::hidden('curso_id', $curso->id) }}
	</div>
	
	<div class="row" style="padding:5px;">
		{{Form::bsText('id_asignatura_no', $asignatura->descripcion, 'Asignatura', ['disabled'=>'disabled'])}}
		{{ Form::hidden('asignatura_id',$asignatura->id) }}
	</div>

	{{ Form::hidden('aux_curso_id', $curso->id) }}
@endsection