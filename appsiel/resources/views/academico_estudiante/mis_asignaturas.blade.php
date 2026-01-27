@extends('layouts.academico_estudiante')

@section('estilos_1')
<link rel="stylesheet" href="{{asset('assets/css/fullcalendar.min.css')}}">
<style>
	.mis-asignaturas-page {
		padding-top: 30px;
	}

	.mis-asignaturas-hero {
		background: linear-gradient(135deg, #1d3c78, #516de7);
		color: #fff !important;
		border-radius: 1rem;
		padding: 1.6rem 2rem;
		box-shadow: 0 14px 40px rgba(15, 32, 92, 0.2);
		margin-bottom: 1.5rem;
	}

	.mis-asignaturas-hero h2 {
		margin: 0;
		font-weight: 600;
		color: #ffffff;
		text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
	}

	.mis-asignaturas-hero p {
		margin: 0.35rem 0 0;
		color: rgba(255, 255, 255, 0.85);
	}

	.mis-asignaturas-table-card {
		border-radius: 1.1rem;
		box-shadow: 0 20px 45px rgba(20, 33, 86, 0.08);
		border: none;
		overflow: hidden;
	}

	.mis-asignaturas-table-card .card-body {
		padding: 2rem;
	}

	.mis-asignaturas-table-card .table {
		background: #fff;
		border-radius: 0.8rem;
		overflow: hidden;
		box-shadow: 0 8px 20px rgba(15, 32, 92, 0.05);
	}

	.mis-asignaturas-table-card .table thead th {
		background: #f5f6fb;
		color: #d5e2ff;
		text-transform: uppercase;
		font-size: 0.8rem;
		letter-spacing: 0.12em;
		font-weight: 600;
		border: none;
	}

	.mis-asignaturas-table-card .table thead th:first-child {
		border-top-left-radius: 0.8rem;
	}

	.mis-asignaturas-table-card .table thead th:last-child {
		border-top-right-radius: 0.8rem;
	}

	.mis-asignaturas-table-card .table tbody td {
		vertical-align: middle;
		border-color: #edf2f7;
	}

	.mis-asignaturas-table-card .table tbody tr:hover {
		background: #f8f9ff;
	}

	.mis-asignaturas-table-card .btn {
		border-radius: 0.6rem;
	}

	@media (max-width: 768px) {
		.mis-asignaturas-table-card .card-body {
			padding: 1.2rem;
		}
	}
</style>
@endsection

@section('content')

@include('layouts.mensajes')


<div class="container-fluid mis-asignaturas-page">
	<div class="mis-asignaturas-hero">
		<h2>Mis asignaturas</h2>
		<p>Explora tus actividades escolares, guías académicas y foros disponibles para este curso.</p>
	</div>
	<div class="card mis-asignaturas-table-card">
		<div class="card-body">
			<div class="table-responsive" id="table_content">
			<table class="table table-striped" id="myTable">
				{{ Form::bsTableHeader(['Asignatura','Enlaces']) }}
				<tbody>

					@foreach ($asignaturas as $fila)
					<tr>
						<td>
							{{ $fila->descripcion }}
						</td>
						<td>
							<a href="{{url('academico_estudiante/actividades_escolares/'.$curso_id.'/'.$fila->id.'?id='.Input::get('id'))}}"
								class="btn btn-sm btn-success"><i class="fa fa-edit"></i> Actividades escolares </a>

							&nbsp;&nbsp;&nbsp;

							@if( config('calificaciones.estudiante_revisar_guia_academicas') == 'Si' )

							<a href="{{ url('academico_estudiante/guias_planes_clases/'.$curso_id.'/'.$fila->id.'?id='.Input::get('id'))}}"
								class="btn btn-sm btn-danger"><i class="fa fa-book"></i> Guías académicas </a>

							&nbsp;&nbsp;&nbsp;
							@endif

							@if( config('calificaciones.estudiante_activar_foros_discucion') == 'Si' )

							<a href="{{ url( 'foros/'.$curso_id.'/'.$fila->id.'/'.$periodo_lectivo->id.'/inicio?id='.Input::get('id') ) }}"
								class="btn btn-sm btn-info" target="_blank"><i class="fa fa-bullhorn"></i> Foros de
								discusión </a>
							@endif
						</td>
					</tr>
					@endforeach

				</tbody>
			</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('scripts')
<script src="{{asset('assets/js/fullcalendar/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/moment.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/fullcalendar.min.js')}}"></script>
<script src="{{asset('assets/js/fullcalendar/es.js')}}"></script>
<script>
	$(document).ready( function () {
			$('#calendar').fullCalendar({
				header: {
		            left: 'prev,next today',
		            center: 'title',
		            right: 'month,agendaWeek,agendaDay,listMonth'
		          },

		        defaultView: 'agendaWeek',
		        minTime:'06:00:00',
		        maxTime:'18:00:00',

		        eventSources: [    
		          	// your event source
				    {
				      url: '../get_eventos',
				      textColor: 'black'
				    }
				    // any other sources...
				  ]
				
			})
		});
</script>
@endsection
