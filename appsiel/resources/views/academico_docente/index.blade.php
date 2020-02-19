@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-lg-10 col-lg-offset-1">
			<div class="panel panel-primary">
				<div class="panel-heading" align="center">
					<h4>
						Mis asignaturas por curso
						<br>
						Año lectivo: {{$periodo_lectivo->descripcion}}
					</h4>
				</div>
				<div class="panel-body">
					@if( !is_null($listado) )
					<table class="table table-responsive">
						<thead>
							<tr>
								<th>Curso</th>
								<th>Asignatura</th>
								<th>Acción</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($listado as $fila)
								<tr>
									<td>{{ $fila->Curso }}</td>
									<td>{{ $fila->Asignatura }}</td>
									<td>
										<!-- academico_docente/asistencia_clases -->
										<a href="{{ url('academico_docente/asistencia_clases?id='.Input::get('id').'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura.'&id='.Input::get('id')) }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> Asistencia</a>
										
									  	&nbsp;

									  	<!--
												PLAN DE CLASES
									  	-->
									  	{{ Form::bsBtnDropdown( 'Planes Clases', 'primary', 'edit', [ ['link' => 'web/create?id='.Input::get('id').'&id_modelo='.$modelo_plan_clases_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 'etiqueta' => 'Ingresar'], ['link' => 'web?id='.Input::get('id').'&id_modelo='.$modelo_plan_clases_id, 'etiqueta' => 'Consultar' ] ] ) }}
										
									  	&nbsp;

									  	<!--
												CALIFICACIONES
									  	-->
									  	{{ Form::bsBtnDropdown( 'Calificaciones', 'primary', 'edit', [ ['link' => 'academico_docente/calificar/'.$fila->curso_id.'/'.$fila->id_asignatura.'/'.rand(0,1000).'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_calificaciones/curso_id/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}

									  	{{ Form::bsBtnDropdown( 'Logros', 'success', 'tag', [ ['link' => 'academico_docente/ingresar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_id, 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_id, 'etiqueta' => 'Consultar' ] ] ) }}
									  	
									  	@can('ACDO_metas_propositos')
									  		{{ Form::bsBtnDropdown( 'Propósitos', 'info', 'tag', [ ['link' => 'academico_docente/ingresar_metas/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_metas/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
									  	@endcan

									    &nbsp;
									  	<a href="{{ url('academico_docente/revisar_estudiantes/curso_id/'.$fila->curso_id.'/id_asignatura/'.$fila->id_asignatura.'?id='.Input::get('id')) }}" class="btn btn-sm btn-warning"><i class="fa fa-users"></i> Estudiantes</a>
									  	
									  	@can('ACDO_control_disciplinario')
									  		{{ Form::bsBtnDropdown( 'Control disciplinario', 'danger', 'eye', [ ['link' => 'matriculas/control_disciplinario/precreate/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'matriculas/control_disciplinario/consultar/'.$fila->curso_id.'/'.date('Y-m-d').'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
									  	@endcan

									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
					@else
						<h3><i class="fa fa-warning"> </i> Aún no tiene carga académica asignada.</h3>
					@endif
				</div>
			</div>
		</div>
	</div>
@endsection