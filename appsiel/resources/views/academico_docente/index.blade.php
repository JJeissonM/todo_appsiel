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
								<th></th>
								<th>Curso</th>
								<th width="200px">Asignatura</th>
								<th>Acción</th>
							</tr>
						</thead>
						<tbody>
							<?php 
								$contador = 1;
							?>
							@foreach ($listado as $fila)
								<tr>
									<td>{{ $contador }}</td>
									<td>{{ $fila->Curso }}</td>
									<td width="200px">{{ $fila->Asignatura }}</td>
									<td>
										<!-- academico_docente/asistencia_clases -->
										<a href="{{ url('academico_docente/asistencia_clases?id='.Input::get('id').'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura.'&id='.Input::get('id')) }}" class="btn btn-sm btn-default"><i class="fa fa-list"></i> Asistencia</a>
										
									  	&nbsp;

									  	<!--
												PLAN DE CLASES
									  	-->
									  	{{ Form::bsBtnDropdown( 
									  		'Planes Clases', 
									  		'danger', 
									  		'edit', 
									  		[ 
									  			[
									  				'link' => 'web/create?id='.Input::get('id').'&id_modelo='.$modelo_plan_clases_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura,
									  				'etiqueta' => 'Ingresar plan de clases' ],
									  			[
									  				'link' => 'web?id='.Input::get('id').'&id_modelo='.$modelo_plan_clases_id, 
									  				'etiqueta' => 'Consultar planes' ],
									  			[
									  				'link' => '#', 
									  				'etiqueta' => '--------' ],
									  			[
									  				'link' => 'web/create?id='.Input::get('id').'&id_modelo='.$modelo_guia_academica_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 
									  				'etiqueta' => 'Ingresar guía académica' ],
									  			[
									  				'link' => 'web?id='.Input::get('id').'&id_modelo='.$modelo_guia_academica_id, 
									  				'etiqueta' => 'Consultar guías' ]
									  		] ) 
									  	}}
										
									  	&nbsp;

									  	<!--
												CALIFICACIONES
									  	-->
									  	<?php 
									  		$modelo_preinforme_academico_id = 192;
									  	?>
									  	@if( config('calificaciones.manejar_preinformes_academicos') == 'Si' )
									  		{{ Form::bsBtnDropdown( 'Calificaciones', 'primary', 'edit', [ ['link' => 'academico_docente/calificar/'.$fila->curso_id.'/'.$fila->id_asignatura.'/'.rand(0,1000).'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_calificaciones/curso_id/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ], ['link' => 'cali_preinforme_academico/create?id='.Input::get('id').'&id_modelo='.$modelo_preinforme_academico_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 'etiqueta' => 'Pre-informe: ingresar' ], ['link' => 'web?id='.Input::get('id').'&id_modelo='.$modelo_preinforme_academico_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 'etiqueta' => 'Pre-informe: consultar' ] ] ) }}
									  	@else
									  		{{ Form::bsBtnDropdown( 'Calificaciones', 'primary', 'edit', [ ['link' => 'academico_docente/calificar/'.$fila->curso_id.'/'.$fila->id_asignatura.'/'.rand(0,1000).'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_calificaciones/curso_id/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
									  	@endif


									  	{{ Form::bsBtnDropdown( 'Logros', 'success', 'tag', [ 
									  			['link' => 'academico_docente/ingresar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_id, 'etiqueta' => 'Ingresar'],
									  			['link' => 'academico_docente/revisar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_id, 'etiqueta' => 'Consultar' ],
									  			[
									  				'link' => '#', 
									  				'etiqueta' => '--------' ],
									  			['link' => 'academico_docente/ingresar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_adicionales_id, 'etiqueta' => 'Logros adicionales'],
									  			['link' => 'academico_docente/revisar_logros/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id').'&id_modelo='.$modelo_logros_adicionales_id, 'etiqueta' => 'Consultar adicionales'] ] ) }}
									  	

									  	@if( config('calificaciones.colegio_maneja_metas') == 'Si' )
										  	@can('ACDO_metas_propositos')
										  		{{ Form::bsBtnDropdown( 'Propósitos', 'info', 'tag', [ ['link' => 'academico_docente/ingresar_metas/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'academico_docente/revisar_metas/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
										  	@endcan
										@endif

									    &nbsp;
									  	<a href="{{ url('academico_docente/revisar_estudiantes/curso_id/'.$fila->curso_id.'/id_asignatura/'.$fila->id_asignatura.'?id='.Input::get('id')) }}" class="btn btn-sm btn-warning"><i class="fa fa-users"></i> Estudiantes</a>
									  	
									  	@can('ACDO_control_disciplinario')
									  		{{ Form::bsBtnDropdown( 'Control disciplinario', 'danger', 'eye', [ ['link' => 'matriculas/control_disciplinario/precreate/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'matriculas/control_disciplinario/consultar/'.$fila->curso_id.'/'.date('Y-m-d').'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
									  	@endcan

									  	<a href="{{ url( 'foros/'.$fila->curso_id.'/'.$fila->id_asignatura.'/'.$periodo_lectivo->id.'/inicio?id='.Input::get('id') ) }}" class="btn btn-sm btn-info"><i class="fa fa-bullhorn"></i> FOROS </a>

									</td>
								</tr>
								<?php 
									$contador++;
								?>
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