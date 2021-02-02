@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="row">
	<div class="col-lg-10 col-lg-offset-1">
		<div class="panel panel-success">
			<div class="panel-heading" align="center" style="background: #42A3DC !important;">
				<h4 style="color: #fff;">
					CARGA ACADÉMICA
					<br>
					AÑO LECTIVO: {{$periodo_lectivo->descripcion}}
				</h4>
			</div>
			<div class="panel-body">
				@if( !is_null($listado) )

				<div class="col-md-12 botones-gmail">
					<!-- ASISTENCIA A CLASE -->
					<a class="btn-gmail" onclick="asistencia()" title="Asistencia"><i class="fa fa-list"></i></a>
					<a class="btn-gmail" onclick="planClaseCrear()" title="Ingresar Plan de Clases"><i class="fa fa-plus"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_plan_clases_id}}" title="Consultar Plan de clases"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" onclick="guiaCrear()" title="Ingresar Guía Académica"><i class="fa fa-book"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" onclick="listarEstudiantes()" title="Listado de Estudiante"><i class="fa fa-users"></i></a>
					<a class="btn-gmail" onclick="foros()" title="Foros de Discusión"><i class="fa fa-bullhorn"></i></a>
				</div>

				<table class="table table-responsive">
					<thead>
						<tr>
							<th><i class="fa fa-check-square-o"></i></th>
							<th>CURSO O GRUPO</th>
							<th>ASIGNATURA</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$contador = 1;
						?>
						@foreach ($listado as $fila)
						<tr>
							<td><input type="checkbox" value="{{$fila->curso_id.';'.$fila->id_asignatura}}" class="btn-gmail-check"></td>
							<td>{{ $fila->Curso }}</td>
							<td>{{ $fila->Asignatura }}</td>
							<td>
								<!--
												CALIFICACIONES
									  	-->
								<?php
								$modelo_preinforme_academico_id = 192;

								$opcion_ingresar_calificaciones = ['link' => 'academico_docente/calificar/' . $fila->curso_id . '/' . $fila->id_asignatura . '/' . rand(0, 1000) . '?id=' . Input::get('id'), 'etiqueta' => 'Ingresar'];

								$opcion_consultar_calificaciones = ['link' => 'academico_docente/revisar_calificaciones/curso_id/' . $fila->curso_id . '/' . $fila->id_asignatura . '?id=' . Input::get('id'), 'etiqueta' => 'Consultar'];


								$opcion_ingresar_notas_nivelaciones = ['link' => 'sga_ingresar_notas_nivelaciones/' . $fila->curso_id . '/' . $fila->id_asignatura . '?id=' . Input::get('id'), 'etiqueta' => 'Ingresar nivelaciones'];

								$opcion_consultar_notas_nivelaciones = ['link' => 'sga_notas_nivelaciones_revisar/' . $fila->curso_id . '/' . $fila->id_asignatura . '?id=' . Input::get('id'), 'etiqueta' => 'Consultar nivelaciones'];

								?>
								@if( config('calificaciones.manejar_preinformes_academicos') == 'Si' )
								{{ Form::bsBtnDropdown( 'Calificaciones', 'primary', 'edit', [ 
										  			$opcion_ingresar_calificaciones, 
										  			$opcion_consultar_calificaciones, 
										  			['link' => 'cali_preinforme_academico/create?id='.Input::get('id').'&id_modelo='.$modelo_preinforme_academico_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 'etiqueta' => 'Pre-informe: ingresar' ], 
										  			['link' => 'web?id='.Input::get('id').'&id_modelo='.$modelo_preinforme_academico_id.'&curso_id='.$fila->curso_id.'&asignatura_id='.$fila->id_asignatura, 'etiqueta' => 'Pre-informe: consultar' ],
										  			[
										  				'link' => '#', 
										  				'etiqueta' => '--------' ],
										  			$opcion_ingresar_notas_nivelaciones,
										  			$opcion_consultar_notas_nivelaciones
									  			] ) }}
								@else
								{{ Form::bsBtnDropdown( 'Calificaciones', 'primary', 'edit', [ 
										  			$opcion_ingresar_calificaciones, 
										  			$opcion_consultar_calificaciones,
										  			[
										  				'link' => '#', 
										  				'etiqueta' => '--------' ],
										  			$opcion_ingresar_notas_nivelaciones,
										  			$opcion_consultar_notas_nivelaciones
									  			] ) }}
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


								@can('ACDO_control_disciplinario')
								{{ Form::bsBtnDropdown( 'Control disciplinario', 'danger', 'eye', [ ['link' => 'matriculas/control_disciplinario/precreate/'.$fila->curso_id.'/'.$fila->id_asignatura.'?id='.Input::get('id'), 'etiqueta' => 'Ingresar'], ['link' => 'matriculas/control_disciplinario/consultar/'.$fila->curso_id.'/'.date('Y-m-d').'?id='.Input::get('id'), 'etiqueta' => 'Consultar' ] ] ) }}
								@endcan
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

@section('scripts')

<script type="text/javascript">
	$(document).ready(function() {
		//
	});


	function mensaje(title, message, type) {
		Swal.fire(
			title,
			message,
			type
		)
	}

	function getElementos() {
		let elementos = [];
		$("input[type=checkbox]:checked").each(function() {
			elementos.push($(this).val());
		});
		return elementos;
	}

	function asistencia() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('academico_docente/asistencia_clases?id='.Input::get('id'))}}";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + '&curso_id=' + curso_asignatura[0] + '&asignatura_id=' + curso_asignatura[1] + "&id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar asistencia un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function planClaseCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('web/create')}}?id={{Input::get('id')}}&id_modelo={{$modelo_plan_clases_id}}";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + '&curso_id=' + curso_asignatura[0] + '&asignatura_id=' + curso_asignatura[1];
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function guiaCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('web/create')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + '&curso_id=' + curso_asignatura[0] + '&asignatura_id=' + curso_asignatura[1];
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function listarEstudiantes() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/revisar_estudiantes/curso_id/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/id_asignatura/" + curso_asignatura[1] + "?id={{Input::get('id')}};";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function foros() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/foros/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "/{{$periodo_lectivo->id}}/inicio?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}
</script>

@endsection