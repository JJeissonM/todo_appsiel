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
				<?php
				$modelo_preinforme_academico_id = 192;
				?>

				<div class="col-md-12 botones-gmail">
					<!-- ASISTENCIA A CLASE -->
					<a class="btn-gmail" onclick="asistencia()" title="Asistencia"><i class="fa fa-list"></i></a>
					<!-- PLANES DE CLASES Y GUÍAS ACADÉMICAS -->
					<a class="btn-gmail" onclick="planClaseCrear()" title="Ingresar Plan de Clases"><i class="fa fa-plus"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_plan_clases_id}}" title="Consultar Plan de clases"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" onclick="guiaCrear()" title="Ingresar Guía Académica"><i class="fa fa-book"></i></a>
					<a class="btn-gmail" href="{{url('web')}}?id={{Input::get('id')}}&id_modelo={{$modelo_guia_academica_id}}" title="Consultar Guía Académica"><i class="fa fa-search"></i></a>
					<!-- CALIFICACIONES -->
					<a class="btn-gmail" onclick="calificacionesCrear()" title="Ingresar Calificaciones"><i class="fa fa-list-ol"></i></a>
					<a class="btn-gmail" onclick="calificacionesConsultar()" title="Consultar Calificaciones"><i class="fa fa-search"></i></a>
					@if( config('calificaciones.manejar_preinformes_academicos') == 'Si' )
					<a class="btn-gmail" onclick="preinformeCrear()" title="Ingresar Pre-Informe"><i class="fa fa-file-pdf-o"></i></a>
					<a class="btn-gmail" onclick="preinformeConsultar()" title="Consultar Pre-Informe"><i class="fa fa-search"></i></a>
					@endif
					<a class="btn-gmail" onclick="nivelacionesCrear()" title="Ingresar Nivelaciones"><i class="fa fa-check"></i></a>
					<a class="btn-gmail" onclick="nivelacionesConsultar()" title="Consultar Nivelaciones"><i class="fa fa-search"></i></a>
					<!-- LOGROS -->
					<a class="btn-gmail" onclick="logrosCrear()" title="Crear Logros"><i class="fa fa-bookmark-o"></i></a>
					<a class="btn-gmail" onclick="logrosConsultar()" title="Consultar Logros"><i class="fa fa-search"></i></a>
					<a class="btn-gmail" onclick="logrosAdicionalesCrear()" title="Crear Logros Adicionales"><i class="fa fa-tag"></i></a>
					<a class="btn-gmail" onclick="logrosAdicionalesConsultar()" title="Consultar Logros Adicionales"><i class="fa fa-search"></i></a>
					<!-- PROPÓSITOS -->
					@if( config('calificaciones.colegio_maneja_metas') == 'Si' )
					@can('ACDO_metas_propositos')
					<a class="btn-gmail" onclick="propositoCrear()" title="Crear Meta o Propósito"><i class="fa fa-check-square-o"></i></a>
					<a class="btn-gmail" onclick="propositoConsultar()" title="Consultar Meta o Propósito"><i class="fa fa-search"></i></a>
					@endcan
					@endif
					@can('ACDO_control_disciplinario')
					<a class="btn-gmail" onclick="controlDisciplinarioCrear()" title="Crear Control Disciplinario"><i class="fa fa-eye"></i></a>
					<a class="btn-gmail" onclick="controlDisciplinarioConsultar()" title="Consultar Control Disciplinario"><i class="fa fa-search"></i></a>
					@endcan
					<!-- LISTADO DE ESTUDIANTES Y FOROS -->
					<a class="btn-gmail" onclick="listarEstudiantes()" title="Listado de Estudiante"><i class="fa fa-users"></i></a>
					<a class="btn-gmail" onclick="foros()" title="Foros de Discusión"><i class="fa fa-bullhorn"></i></a>
				</div>

				<table class="table table-responsive">
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
</script>

@endsection