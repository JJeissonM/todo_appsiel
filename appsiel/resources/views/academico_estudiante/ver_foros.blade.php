@extends('layouts.principal')

@section('estilos_1')
	<link rel="stylesheet" href="{{asset('assets/css/fullcalendar.min.css')}}">
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')


	<div class="table-responsive" id="table_content">
		<table class="table table-bordered table-striped" id="myTable">
			{{ Form::bsTableHeader(['ASIGNATURA','INGRESAR']) }}
			<tbody>

				@foreach ($asignaturas as $fila)
					<tr>
							<td>
								{{ $fila->descripcion }}
							</td>
							<td>
								<a href="{{ url( 'foros/'.$curso_id.'/'.$fila->id.'/'.$periodo_lectivo->id.'/inicio?id='.Input::get('id') ) }}" class="btn btn-sm btn-info"><i class="fa fa-bullhorn"></i> Ingresar </a>
							</td>
					</tr>
				@endforeach
				
			</tbody>
		</table>
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