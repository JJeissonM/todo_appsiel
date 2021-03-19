@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		.btn-block{
			font-size: 12px;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-success">
			<div class="panel-heading" align="center" style="background: #42A3DC !important;">
				<h4 style="color: #fff;">
					CARGA ACADÉMICA
					<br>
					AÑO LECTIVO: {{$periodo_lectivo->descripcion}}
				</h4>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-md-4">
						@if( !is_null($listado) )
							<?php
								$modelo_preinforme_academico_id = 192;
							?>
							<table class="table table-responsive" id="myTable2">
								<thead>
									<tr>
										<th><i class="fa fa-check-square-o"></i></th>
										<th>#</th>
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
										<td>{{$contador}}</td>
										<td>{{ $fila->Curso }}</td>
										<td>{{ $fila->Asignatura }}</td>
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

					<div class="col-md-8">
						<table class="table table-responsive">
							<thead>
								<tr>
									<th>ACCIONES</th>
								</tr>
							</thead>
						</table>
						<div class="col-md-12 botones-gmail">
							<!-- ASISTENCIA A CLASE -->
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_asistencia" aria-expanded="false" aria-controls="collapse_asistencia">
									ASISTENCIA A CLASE <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_asistencia">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="asistencia()" title="Asistencia"><i class="fa fa-list"></i> Asistencia</a></li>
										</ul>
									</div>
								</div>
							</div>
							<!-- PLANES DE CLASES Y GUÍAS ACADÉMICAS -->
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_planes" aria-expanded="false" aria-controls="collapse_planes">
									PLANES DE CLASES Y GUÍAS ACADÉMICAS <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_planes">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="planClaseCrear()" title="Ingresar Plan de Clases"><i class="fa fa-plus"></i> Ingresar Plan de Clases</a></li>
											<li><a href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_plan_clases_id}}" title="Consultar Plan de clases"><i class="fa fa-search"></i> Consultar Planes de clases</a></li>
											<li><a style="cursor: pointer;" onclick="guiaCrear()" title="Ingresar Guía Académica"><i class="fa fa-book"></i> Ingresar Guía Académica</a></li>
											<li><a href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i> Consultar Guía Académica</a></li>
										</ul>
									</div>
								</div>
							</div>
							<!-- CALIFICACIONES -->
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_calificaciones" aria-expanded="false" aria-controls="collapse_calificaciones">
									CALIFICACIONES <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_calificaciones">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="calificacionesCrear()" title="Ingresar Calificaciones"><i class="fa fa-list-ol"></i> Ingresar Calificaciones</a></li>
											<li><a style="cursor: pointer;" onclick="calificacionesConsultar()" title="Consultar Calificaciones"><i class="fa fa-search"></i> Consultar Calificaciones</a></li>
											@if( config('calificaciones.manejar_preinformes_academicos') == 'Si' )
											<li><a style="cursor: pointer;" onclick="preinformeCrear()" title="Ingresar Pre-Informe"><i class="fa fa-file-pdf-o"></i> Ingresar Pre-Informe</a></li>
											<li><a style="cursor: pointer;" onclick="preinformeConsultar()" title="Consultar Pre-Informe"><i class="fa fa-search"></i> Consultar Pre-Informe</a></li>
											@endif
											<li><a style="cursor: pointer;" onclick="nivelacionesCrear()" title="Ingresar Nivelaciones"><i class="fa fa-check"></i> Ingresar Nivelaciones</a></li>
											<li><a style="cursor: pointer;" onclick="nivelacionesConsultar()" title="Consultar Nivelaciones"><i class="fa fa-search"></i> Consultar Nivelaciones</a></li>
										</ul>
									</div>
								</div>
							</div>
							<!-- LOGROS -->
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_logros" aria-expanded="false" aria-controls="collapse_logros">
									LOGROS <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_logros">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="logrosCrear()" title="Crear Logros"><i class="fa fa-bookmark-o"></i> Crear Logros</a></li>
											<li><a style="cursor: pointer;" onclick="logrosConsultar()" title="Consultar Logros"><i class="fa fa-search"></i> Consultar Logros</a></li>
											<li><a style="cursor: pointer;" onclick="logrosAdicionalesCrear()" title="Crear Logros Adicionales"><i class="fa fa-tag"></i> Crear Logros Adicionales</a></li>
											<li><a style="cursor: pointer;" onclick="logrosAdicionalesConsultar()" title="Consultar Logros Adicionales"><i class="fa fa-search"></i> Consultar Logros Adicionales</a></li>
										</ul>
									</div>
								</div>
							</div>
							<!-- PROPÓSITOS -->
							@if( config('calificaciones.colegio_maneja_metas') == 'Si' )
							@can('ACDO_metas_propositos')
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_metas" aria-expanded="false" aria-controls="collapse_metas">
									METAS O PROPÓSITOS <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_metas">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="propositoCrear()" title="Crear Meta o Propósito"><i class="fa fa-check-square-o"></i> Crear Meta o Propósito</a></li>
											<li><a style="cursor: pointer;" onclick="propositoConsultar()" title="Consultar Meta o Propósito"><i class="fa fa-search"></i> Consultar Meta o Propósito</a></li>
										</ul>
									</div>
								</div>
							</div>
							@endcan
							@endif
							@can('ACDO_control_disciplinario')
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_control" aria-expanded="false" aria-controls="collapse_control">
									CONTROL DISCIPLINARIO <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_control">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="controlDisciplinarioCrear()" title="Crear Control Disciplinario"><i class="fa fa-eye"></i> Crear Control Disciplinario</a></li>
											<li><a style="cursor: pointer;" onclick="controlDisciplinarioConsultar()" title="Consultar Control Disciplinario"><i class="fa fa-search"></i> Consultar Control Disciplinario</a></li>
										</ul>
									</div>
								</div>
							</div>
							@endcan
							<!-- LISTADO DE ESTUDIANTES Y FOROS -->
							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_foros" aria-expanded="false" aria-controls="collapse_foros">
									ESTUDIANTES Y FOROS <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_foros">
									<div class="well">
										<ul style="list-style: none;">
											<li><a style="cursor: pointer;" onclick="listarEstudiantes()" title="Listado de Estudiante"><i class="fa fa-users"></i> Listado de Estudiante</a></li>
											<li><a style="cursor: pointer;" onclick="foros()" title="Foros de Discusión"><i class="fa fa-bullhorn"></i> Foros de Discusión</a></li>
										</ul>
									</div>
								</div>
							</div>

							<div class="col-md-4">
								<a class="btn btn-default btn-block" style="margin-bottom: 30px; color: #000 !important; border: 2px solid; border-color: #999 !important;" role="button" data-toggle="collapse" href="#collapse_evaluacion_por_aspectos" aria-expanded="false" aria-controls="collapse_evaluacion_por_aspectos">
									EVALUACIÓN POR ASPECTOS <i class="fa fa-arrow-down"></i></a>
								<div class="collapse" id="collapse_evaluacion_por_aspectos">
									<div class="well">
										<ul style="list-style: none;">
											<li>{{ Form::date('fecha_valoracion', date('Y-m-d'), ['class' => 'form-control','id' => 'fecha_valoracion'], [] ) }}</li>
											<li><a style="cursor: pointer;" onclick="evaluacionAspectosCrear()" title="Ingresar evaluación por aspectos"><i class="fa fa-users"></i> Ingresar Evaluación </a></li>
											<li><a style="cursor: pointer;" href="{{url('/index_procesos/matriculas.procesos.consolidado_evaluacion_por_aspectos?id=' . Input::get('id') )}}" title="Generar consolidados"><i class="fa fa-users"></i> Generar consolidados </a></li>
										</ul>
									</div>
								</div>
							</div>
						</div>
					</div> <!-- columna botones gmail -->

				</div>
				
			</div> <!-- panel body -->
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

	function calificacionesCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/calificar/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "/{{rand(0, 1000)}}?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function calificacionesConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/revisar_calificaciones/curso_id/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function preinformeCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/cali_preinforme_academico/create";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + "?id={{Input::get('id')}}&id_modelo={{$modelo_preinforme_academico_id}}&curso_id=" + curso_asignatura[0] + "&asignatura_id=" + curso_asignatura[1];
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function preinformeConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/web";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + "?id={{Input::get('id')}}&id_modelo={{$modelo_preinforme_academico_id}}&curso_id=" + curso_asignatura[0] + "&asignatura_id=" + curso_asignatura[1];
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function nivelacionesCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/sga_ingresar_notas_nivelaciones/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function nivelacionesConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/sga_notas_nivelaciones_revisar/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function logrosCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/ingresar_logros/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}&id_modelo={{$modelo_logros_id}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function logrosConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/revisar_logros/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}&id_modelo={{$modelo_logros_id}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function logrosAdicionalesCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/ingresar_logros/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}&id_modelo={{$modelo_logros_adicionales_id}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function logrosAdicionalesConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/revisar_logros/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}&id_modelo={{$modelo_logros_adicionales_id}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function propositoCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/ingresar_metas/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function propositoConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/academico_docente/revisar_metas/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function controlDisciplinarioCrear() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/matriculas/control_disciplinario/precreate/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function controlDisciplinarioConsultar() {
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/matriculas/control_disciplinario/consultar/";
			if (elementos.length == 1) {
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/{{date('Y-m-d')}}?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	function evaluacionAspectosCrear()
	{
		let elementos = getElementos();
		if (elementos.length > 0) {
			var url = "{{url('')}}/sga_observador_evaluacion_por_aspectos_ingresar_valoracion/";
			if (elementos.length == 1)
			{
				//procesar uno
				var curso_asignatura = elementos[0].split(';');
				url = url + curso_asignatura[0] + "/" + curso_asignatura[1] + "/" + $('#fecha_valoracion').val() + "?id={{Input::get('id')}}";
				location.href = url;
			} else {
				mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			}
		} else {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
		}
	}

	$('#myTable2').DataTable({
				dom: 'Bfrtip',
				"paging": false,
				buttons: [],
				order: [
					[0, 'desc']
				],
				"language": {
					            "search": "Buscar asignatura",
					            "zeroRecords": "Ningún registro encontrado.",
					            "info": "Mostrando página _PAGE_ de _PAGES_",
					            "infoEmpty": "Tabla vacía.",
					            "infoFiltered": "(filtrado de _MAX_ registros totales)"
					        }
			});

</script>

@endsection