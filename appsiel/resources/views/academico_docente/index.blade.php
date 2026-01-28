@extends('layouts.principal')

@section('estilos_1')
	<style>
		.panel-heading.docente-panel-heading {
			background: #42A3DC !important;
			color: #fff;
		}

		.docente-summary {
			margin-bottom: 1.75rem;
		}

		.summary-card {
			background: #f4f8fb;
			border: 1px solid #dfe7f0;
			border-radius: 6px;
			padding: 1.1rem 1.2rem;
			text-transform: uppercase;
			font-size: 1.3rem;
			color: #101d2e;
		}

		.summary-card span {
			font-size: 1.15rem;
			letter-spacing: 0.12em;
		}

		.summary-card strong {
			font-size: 1.95rem;
			display: block;
			margin-top: 0.35rem;
			color: #1f3c88;
		}

		.action-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 1rem;
		}

		.action-card {
			border: 1px solid #e4e7ed;
			border-radius: 0.4rem;
			background: #fff;
			box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
			display: flex;
			flex-direction: column;
		}

		.action-card__header {
			background: #f9fbfd;
			border-bottom: 1px solid #e4e7ed;
			padding: 0.85rem 1rem;
			font-weight: 700;
			text-transform: uppercase;
			font-size: 2rem;
			color: #23385c;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}

		.action-card__body {
			padding: 1.1rem;
			flex: 1;
		}

		.action-card__body p {
			margin-bottom: 0.75rem;
			font-size: 1.35rem;
			color: #4f5c70;
		}

		.action-card__list {
			list-style: none;
			padding: 0;
			margin: 0;
		}

		.action-card__list li + li {
			margin-top: 0.7rem;
		}

		.action-card__btn {
			width: 100%;
			text-align: left;
			border-radius: 0.3rem;
			border: 2px solid transparent;
			transition: all 0.2s ease;
			background: #f5f7fb;
			color: #1f3c88;
			display: inline-flex;
			align-items: center;
			justify-content: space-between;
			padding: 0.85rem 1rem;
			font-weight: 600;
			font-size: 1.5rem;
			cursor: pointer;
			letter-spacing: 0.02em;
			line-height: 1.4;
		}

		.action-card__btn:hover,
		.action-card__btn:focus {
			border-color: #42a3dc;
			background: #ffffff;
			color: #1c2c66;
		}

		.table.table-responsive {
			margin-bottom: 0;
		}

		.tabla-col {
			flex: 0 0 32%;
			max-width: 32%;
		}

		.cursos-buscador {
			display: flex;
			align-items: center;
			gap: 0.75rem;
			padding-bottom: 0.4rem;
			border-bottom: 1px solid #dfe3ea;
			margin-bottom: 1rem;
		}

		.cursos-buscador i {
			color: #42a3dc;
			font-size: 1.5rem;
		}

		.cursos-buscador input {
			border: 0;
			padding: 0.4rem;
			font-size: 1.4rem;
			flex: 1;
			background: transparent;
		}

		.cursos-buscador input:focus {
			outline: none;
			border-bottom: 1px solid #42a3dc;
		}

		#cursosAsignadosTable {
			font-size: 1.35rem;
		}

		#cursosAsignadosTable thead,
		#cursosAsignadosTable.dataTable thead,
		#cursosAsignadosTable_wrapper .dataTables_scrollHeadInner thead {
			background-color: #f5f7fb !important;
			color: #23385c;
		}

		#cursosAsignadosTable thead th,
		#cursosAsignadosTable.dataTable thead th,
		#cursosAsignadosTable_wrapper .dataTables_scrollHeadInner thead th {
			border-bottom: 1px solid #dfe3ea;
			font-weight: 700;
			letter-spacing: 0.08em;
			background-color: #f5f7fb !important;
			color: #1f3c88;
		}

		#cursosAsignadosTable tbody td {
			padding: 0.65rem 0.65rem;
		}

		@media (max-width: 992px) {
			.tabla-col {
				flex: 0 0 100%;
				max-width: 100%;
			}
		}

		.table > tbody > tr:hover {
			background-color: #f4f9ff;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

@php
	$cursos = collect($listado ?? []);
	$manejarDesempenios = config('calificaciones.manejar_calificaciones_por_niveles_de_desempenios') === 'Si';
	$manejarPreinformes = config('calificaciones.manejar_preinformes_academicos') === 'Si';
	$manejarMetas = config('calificaciones.colegio_maneja_metas') === 'Si';
	$maneja_evaluacionAspectos = config('calificaciones.maneja_evaluacionAspectos') === 'Si';
@endphp

<div class="row docente-summary">
	<div class="col-sm-4">
		<div class="summary-card">
			<span>Docente</span>
			<strong>{{ $usuario->name }}</strong>
			<small>{{ $usuario->email }}</small>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="summary-card">
			<span>Periodo Lectivo</span>
			<strong>{{ $periodo_lectivo->descripcion }}</strong>
			<small>Periodo activo del sistema</small>
		</div>
	</div>
	<div class="col-sm-4">
		<div class="summary-card">
			<span>Cursos asignados</span>
			<strong>{{ $cursos->count() }}</strong>
			<small>Selecciona uno para desbloquear las acciones</small>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="panel panel-success">
			<div class="panel-heading docente-panel-heading text-center">
				<h4>
					CARGA ACADÉMICA<br>
					<small style="color: #1c2c66">PERIODO LECTIVO: {{ $periodo_lectivo->descripcion }}</small>
				</h4>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-4 col-md-5 tabla-col">
						<div class="card border-0 shadow-sm" style="border-radius: 0.6rem;">
							<div class="card-header bg-white" style="border-radius: 0.6rem 0.6rem 0 0; padding: 1rem 1.25rem;">
								<div class="d-flex flex-column">
									<span class="text-uppercase font-weight-bold small mb-1" style="letter-spacing: 0.1em;">Cursos asignados</span>
									<span class="text-muted small">Selecciona uno para usar las acciones</span>
								</div>
							</div>
							<div class="card-body" style="padding: 1.1rem;">
								<div class="cursos-buscador">
									<i class="fa fa-search" aria-hidden="true"></i>
									<input id="buscadorCursos" type="text" placeholder="Buscar asignatura" autocomplete="off" aria-label="Buscar asignatura">
								</div>
								@if ($cursos->count())
									<div class="table-responsive">
										<table id="cursosAsignadosTable">
											<thead>
												<tr>
													<th><i class="fa fa-check-square-o"></i></th>
													<th class="text-center">#</th>
													<th>CURSO</th>
													<th>ASIGNATURA</th>
												</tr>
											</thead>
											<tbody>
												@foreach ($cursos as $indice => $fila)
													<tr>
														<td>
															<input type="checkbox" value="{{ $fila->curso_id . ';' . $fila->id_asignatura }}" class="btn-gmail-check">
														</td>
														<td class="text-center">{{ $indice + 1 }}</td>
														<td>{{ $fila->Curso }}</td>
														<td>{{ $fila->Asignatura }}</td>
													</tr>
												@endforeach
											</tbody>
										</table>
									</div>
								@else
									<div class="alert alert-info mb-0">
										<i class="fa fa-info-circle"></i>
										Aún no tiene carga académica asignada.
									</div>
								@endif
							</div>
						</div>
					</div>

					<div class="col-md-7">
						<div class="action-grid">
							<div class="action-card">
								<div class="action-card__header">
									<span>Asistencia a clase</span>
									<i class="fa fa-calendar-check-o"></i>
								</div>
								<div class="action-card__body">
									<p>Registre la asistencia del curso seleccionado para el periodo vigente.</p>
									<ul class="action-card__list">
										<li>
											<button type="button" class="action-card__btn" onclick="asistencia()" title="Registrar asistencia">
												Registrar asistencia <i class="fa fa-angle-right"></i>
											</button>
										</li>
									</ul>
								</div>
							</div>

							<div class="action-card">
								<div class="action-card__header">
									<span>Planes y guías</span>
									<i class="fa fa-book"></i>
								</div>
								<div class="action-card__body">
									<p>Administre planes de clase y guías académicas por curso.</p>
									<ul class="action-card__list">
										<li>
											<button type="button" class="action-card__btn" onclick="planClaseCrear()" title="Ingresar plan de clases">
												Ingresar {{ $modelo_plan_clases->descripcion }} <i class="fa fa-plus"></i>
											</button>
										</li>
										<li>
											<a class="action-card__btn" href="{{ url('web') }}?id={{ Input::get('id') }}&id_modelo={{ $modelo_plan_clases->id }}" title="Consultar planes">
												Consultar {{ $modelo_plan_clases->descripcion }} <i class="fa fa-search"></i>
											</a>
										</li>
										<li>
											<button type="button" class="action-card__btn" onclick="guiaCrear()" title="Ingresar guía académica">
												Ingresar {{ $modelo_guia_academica->descripcion }} <i class="fa fa-book"></i>
											</button>
										</li>
										<li>
											<a class="action-card__btn" href="{{ url('web') }}?id={{ Input::get('id') }}&id_modelo={{ $modelo_guia_academica->id }}" title="Consultar guía">
												Consultar {{ $modelo_guia_academica->descripcion }} <i class="fa fa-search"></i>
											</a>
										</li>
									</ul>
								</div>
							</div>

							<div class="action-card">
								<div class="action-card__header">
									<span>Calificaciones</span>
									<i class="fa fa-list-ol"></i>
								</div>
								<div class="action-card__body">
									<p>Gestione calificaciones, preinformes y nivelaciones.</p>
									<ul class="action-card__list">
										@if ($manejarDesempenios)
											<li>
												<button type="button" class="action-card__btn" onclick="calificacionesCrear_desempenios()">
													Ingresar calificaciones por niveles <i class="fa fa-angle-right"></i>
												</button>
											</li>
										@else
											<li>
												<button type="button" class="action-card__btn" onclick="calificacionesCrear()">
													Ingresar calificaciones <i class="fa fa-angle-right"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="calificacionesConsultar()">
													Consultar calificaciones <i class="fa fa-search"></i>
												</button>
											</li>
										@endif
										@if ($manejarPreinformes)
											<li>
												<button type="button" class="action-card__btn" onclick="preinformeCrear()">
													Ingresar preinforme académico <i class="fa fa-file-pdf-o"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="preinformeConsultar()">
													Consultar preinforme académico <i class="fa fa-search"></i>
												</button>
											</li>
										@endif
										<li>
											<button type="button" class="action-card__btn" onclick="nivelacionesCrear()">
												Ingresar nivelaciones <i class="fa fa-check"></i>
											</button>
										</li>
										<li>
											<button type="button" class="action-card__btn" onclick="nivelacionesConsultar()">
												Consultar nivelaciones <i class="fa fa-search"></i>
											</button>
										</li>
									</ul>
								</div>
							</div>

							<div class="action-card">
								<div class="action-card__header">
									<span>Logros</span>
									<i class="fa fa-star-half-o"></i>
								</div>
								<div class="action-card__body">
									<p>Crear y revisar logros por asignatura.</p>
									<ul class="action-card__list">
										@if ($manejarDesempenios)
											<li>
												<button type="button" class="action-card__btn" onclick="logrosAdicionalesCrear()">
													Crear logros <i class="fa fa-plus"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="logrosAdicionalesConsultar()">
													Consultar logros <i class="fa fa-search"></i>
												</button>
											</li>
										@else
											<li>
												<button type="button" class="action-card__btn" onclick="logrosCrear()">
													Crear logros <i class="fa fa-plus"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="logrosConsultar()">
													Consultar logros <i class="fa fa-search"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="logrosAdicionalesCrear()">
													Crear logros adicionales <i class="fa fa-tag"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="logrosAdicionalesConsultar()">
													Consultar logros adicionales <i class="fa fa-search"></i>
												</button>
											</li>
										@endif
									</ul>
								</div>
							</div>

							@if ($manejarMetas)
								@can('ACDO_metas_propositos')
									<div class="action-card">
										<div class="action-card__header">
											<span>Metas o propósitos</span>
											<i class="fa fa-bullseye"></i>
										</div>
											<div class="action-card__body">
												<p>Defina y revise metas institucionales por curso.</p>
												<ul class="action-card__list">
													<li>
														<button type="button" class="action-card__btn" onclick="propositoCrear()">
															Crear meta o propósito <i class="fa fa-check-square-o"></i>
														</button>
													</li>
													<li>
														<button type="button" class="action-card__btn" onclick="propositoConsultar()">
															Consultar metas o propósitos <i class="fa fa-search"></i>
														</button>
													</li>
												</ul>
											</div>
										</div>
								@endcan
							@endif

							@can('ACDO_control_disciplinario')
								<div class="action-card">
									<div class="action-card__header">
										<span>Control disciplinario</span>
										<i class="fa fa-shield"></i>
									</div>
									<div class="action-card__body">
										<p>Registre y revíse reportes disciplinarios por curso.</p>
										<ul class="action-card__list">
											<li>
												<button type="button" class="action-card__btn" onclick="controlDisciplinarioCrear()">
													Crear control disciplinario <i class="fa fa-eye"></i>
												</button>
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="controlDisciplinarioConsultar()">
													Consultar control disciplinario <i class="fa fa-search"></i>
												</button>
											</li>
										</ul>
									</div>
								</div>
							@endcan

							<div class="action-card">
								<div class="action-card__header">
									<span>Estudiantes y foros</span>
									<i class="fa fa-users"></i>
								</div>
								<div class="action-card__body">
									<p>Acceda al listado de estudiantes o abra los foros de discusión.</p>
									<ul class="action-card__list">
										<li>
											<button type="button" class="action-card__btn" onclick="listarEstudiantes()">
												Listado de estudiantes <i class="fa fa-list"></i>
											</button>
										</li>
										<li>
											<button type="button" class="action-card__btn" onclick="foros()">
												Foros de discusión <i class="fa fa-bullhorn"></i>
											</button>
										</li>
									</ul>
								</div>
							</div>

							@if ($maneja_evaluacionAspectos)
								<div class="action-card">
									<div class="action-card__header">
										<span>Evaluación por aspectos</span>
										<i class="fa fa-sitemap"></i>
									</div>
									<div class="action-card__body">
										<p>Planee observaciones y genere consolidados por curso.</p>
										<ul class="action-card__list">
											<li>
												{{ Form::date('fecha_valoracion', date('Y-m-d'), ['class' => 'form-control', 'id' => 'fecha_valoracion']) }}
											</li>
											<li>
												<button type="button" class="action-card__btn" onclick="evaluacionAspectosCrear()">
													Ingresar evaluación <i class="fa fa-users"></i>
												</button>
											</li>
											<li>
												<a class="action-card__btn" href="{{ url('/index_procesos/matriculas.procesos.consolidado_evaluacion_por_aspectos?id=' . Input::get('id')) }}">
													Generar consolidados <i class="fa fa-users"></i>
												</a>
											</li>
											<li>
												<a class="action-card__btn" href="{{ url('/index_procesos/matriculas.procesos.reporte_consolidados_evaluacion_por_aspectos?id=' . Input::get('id')) }}">
													Reporte de consolidados <i class="fa fa-file-pdf-o"></i>
												</a>
											</li>
											<li>
												<a class="action-card__btn" href="{{ url('/index_procesos/matriculas.procesos.listado_congratulations?id=' . Input::get('id')) }}">
													Listado de Congratulations <i class="fa fa-list"></i>
												</a>
											</li>
											<li>
												<a class="action-card__btn" href="{{ url('/index_procesos/matriculas.procesos.generar_estadisticas_evaluacion_aspectos_por_curso?id=' . Input::get('id')) }}">
													Estadísticas por curso <i class="fa fa-pie-chart"></i>
												</a>
											</li>
										</ul>
									</div>
								</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection
@section('scripts')

<script type="text/javascript">
	const docenteId = "{{ Input::get('id') }}";
	const periodoLectivoId = "{{ $periodo_lectivo->id }}";
	const appUrl = "{{ url('') }}";
	const urlWeb = "{{ url('web/create') }}";
	const fechaHoy = "{{ date('Y-m-d') }}";
	const modeloIds = {
		planClases: {{ $modelo_plan_clases->id ?? 0 }},
		guiaAcademica: {{ $modelo_guia_academica->id ?? 0 }},
		logros: {{ $modelo_logros_id ?? 0 }},
		logrosAdicionales: {{ $modelo_logros_adicionales_id ?? 0 }}
	};
	const modeloPreinformeAcademicoId = {{ $modelo_preinforme_academico_id ?? 192 }};
	const modeloAsistencia = 181;

	const endpoints = {
		asistencia: "{{ url('academico_docente/asistencia_clases') }}",
		planGuia: urlWeb,
		preinformeCrear: "{{ url('cali_preinforme_academico/create') }}",
		calificaciones: `${appUrl}/academico_docente/calificar/`,
		calificacionesDesempenios: `${appUrl}/academico_docente/calificar_desempenios/`,
		calificacionesConsultar: `${appUrl}/academico_docente/revisar_calificaciones/curso_id/`,
		nivelaciones: `${appUrl}/sga_ingresar_notas_nivelaciones/`,
		nivelacionesConsultar: `${appUrl}/sga_notas_nivelaciones_revisar/`,
		logros: `${appUrl}/academico_docente/ingresar_logros/`,
		logrosConsultar: `${appUrl}/academico_docente/revisar_logros/`,
		proposito: `${appUrl}/academico_docente/ingresar_metas/`,
		propositoConsultar: `${appUrl}/academico_docente/revisar_metas/`,
		controlCrear: `${appUrl}/matriculas/control_disciplinario/precreate/`,
		controlConsultar: `${appUrl}/matriculas/control_disciplinario/consultar/`,
		evaluacionAspectos: `${appUrl}/sga_observador_evaluacion_por_aspectos_ingresar_valoracion/`,
		listarEstudiantes: `${appUrl}/academico_docente/revisar_estudiantes/curso_id/`,
		foros: `${appUrl}/foros/`
	};

	const randomSuffix = () => Math.floor(Math.random() * 1000);

	function mensaje(title, message, type) {
		Swal.fire(title, message, type);
	}

	function getElementos() {
		const elementos = [];
		$("input[type=checkbox]:checked").each(function () {
			elementos.push($(this).val());
		});
		return elementos;
	}

	function seleccionarCurso() {
		const elementos = getElementos();
		if (!elementos.length) {
			mensaje('Alerta!', 'Debe seleccionar al menos un registro', 'warning');
			return null;
		}
		if (elementos.length > 1) {
			mensaje('Alerta!', 'Solo puede procesar un curso a la vez', 'warning');
			return null;
		}
		const [curso, asignatura] = elementos[0].split(';');
		return { curso, asignatura };
	}

	function procesarCurso(constructor) {
		const seleccion = seleccionarCurso();
		if (!seleccion) {
			return;
		}
		const url = constructor(seleccion.curso, seleccion.asignatura);
		if (url) {
			location.href = url;
		}
	}

	function asistencia() {
		procesarCurso((curso, asignatura) => `${endpoints.asistencia}?id=${docenteId}&id_modelo=${modeloAsistencia}&curso_id=${curso}&asignatura_id=${asignatura}`);
	}

	function planClaseCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.planGuia}?id=${docenteId}&id_modelo=${modeloIds.planClases}&curso_id=${curso}&asignatura_id=${asignatura}`);
	}

	function guiaCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.planGuia}?id=${docenteId}&id_modelo=${modeloIds.guiaAcademica}&curso_id=${curso}&asignatura_id=${asignatura}`);
	}

	function listarEstudiantes() {
		procesarCurso((curso, asignatura) => `${endpoints.listarEstudiantes}${curso}/id_asignatura/${asignatura}?id=${docenteId}`);
	}

	function foros() {
		procesarCurso((curso, asignatura) => `${endpoints.foros}${curso}/${asignatura}/${periodoLectivoId}/inicio?id=${docenteId}`);
	}

	function calificacionesCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.calificaciones}${curso}/${asignatura}/${randomSuffix()}?id=${docenteId}`);
	}

	function calificacionesCrear_desempenios() {
		procesarCurso((curso, asignatura) => `${endpoints.calificacionesDesempenios}${curso}/${asignatura}/${randomSuffix()}?id=${docenteId}`);
	}

	function calificacionesConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.calificacionesConsultar}${curso}/${asignatura}?id=${docenteId}`);
	}

	function preinformeCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.preinformeCrear}?id=${docenteId}&id_modelo=${modeloPreinformeAcademicoId}&curso_id=${curso}&asignatura_id=${asignatura}`);
	}

	function preinformeConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.planGuia}?id=${docenteId}&id_modelo=${modeloPreinformeAcademicoId}&curso_id=${curso}&asignatura_id=${asignatura}`);
	}

	function nivelacionesCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.nivelaciones}${curso}/${asignatura}?id=${docenteId}`);
	}

	function nivelacionesConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.nivelacionesConsultar}${curso}/${asignatura}?id=${docenteId}`);
	}

	function logrosCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.logros}${curso}/${asignatura}?id=${docenteId}&id_modelo=${modeloIds.logros}`);
	}

	function logrosConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.logrosConsultar}${curso}/${asignatura}?id=${docenteId}&id_modelo=${modeloIds.logros}`);
	}

	function logrosAdicionalesCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.logros}${curso}/${asignatura}?id=${docenteId}&id_modelo=${modeloIds.logrosAdicionales}`);
	}

	function logrosAdicionalesConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.logrosConsultar}${curso}/${asignatura}?id=${docenteId}&id_modelo=${modeloIds.logrosAdicionales}`);
	}

	function propositoCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.proposito}${curso}/${asignatura}?id=${docenteId}`);
	}

	function propositoConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.propositoConsultar}${curso}/${asignatura}?id=${docenteId}`);
	}

	function controlDisciplinarioCrear() {
		procesarCurso((curso, asignatura) => `${endpoints.controlCrear}${curso}/${asignatura}?id=${docenteId}`);
	}

	function controlDisciplinarioConsultar() {
		procesarCurso((curso, asignatura) => `${endpoints.controlConsultar}${curso}/${fechaHoy}?id=${docenteId}`);
	}

	function evaluacionAspectosCrear() {
		procesarCurso((curso, asignatura) => {
			const fechaValoracion = $('#fecha_valoracion').val();
			return `${endpoints.evaluacionAspectos}${curso}/${asignatura}/${fechaValoracion}?id=${docenteId}`;
		});
	}

	$(document).ready(function () {
		if ($('#cursosAsignadosTable').length) {
			const cursosTabla = $('#cursosAsignadosTable').DataTable({
				dom: 'Brtip',
				paging: false,
				buttons: [],
				order: [[0, 'desc']],
				language: {
					search: 'Buscar asignatura',
					zeroRecords: 'Ningún registro encontrado.',
					info: 'Mostrando página _PAGE_ de _PAGES_',
					infoEmpty: 'Tabla vacía.',
					infoFiltered: '(filtrado de _MAX_ registros totales)'
				}
			});

			$('#buscadorCursos').on('input', function () {
				cursosTabla.search(this.value).draw();
			});
		}
	});
</script>

@endsection
