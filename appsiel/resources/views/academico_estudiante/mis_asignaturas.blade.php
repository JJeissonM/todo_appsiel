@extends('layouts.academico_estudiante')

@section('estilos_1')
<link rel="stylesheet" href="{{asset('assets/css/fullcalendar.min.css')}}">
@endsection

@section('content')

@include('layouts.mensajes')


<div class="container-fluid">
	<div class="marco_formulario">
		<div class="table-responsive" id="table_content">
			<table class="table table-bordered table-striped" id="myTable">
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