@extends('layouts.academico_estudiante')

@section('content')

	<div class="box box-primary">
		<div class="box-header with-border">

			@php
				$matricula_activa = $estudiante->matricula_activa();
				$curso_actual = $matricula_activa ? $matricula_activa->curso : null;
				$nivel_actual = $curso_actual ? $curso_actual->descripcion : null;
				$programa_actual = ($curso_actual && $curso_actual->grado) ? $curso_actual->grado->descripcion : null;
			@endphp
			@include('matriculas.estudiantes.datos_basicos', [
						'estudiante' => $estudiante->get_datos_basicos($estudiante->id),
						'curso_actual' => $curso_actual,
						'nivel_actual' => $nivel_actual,
						'programa_actual' => $programa_actual
					]
				)
		</div> <!-- /.box-header -->
	</div> <!-- /.box -->
			
@endsection
