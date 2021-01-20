@extends('layouts.principal')

@section('estilos_1')
	<link rel="stylesheet" href="{{asset('assets/css/fullcalendar.min.css')}}">
@endsection

	@section('content')
	{{ Form::bsMigaPan($miga_pan) }}		
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
		    
		    <!-- <div id='calendar'></div> -->
		    <br>
		     <p style="padding: 10px;"><b>Horario Curso :</b> <br>{{ $curso->descripcion }}</p> 
		    <hr>
		    <div style="margin:20px;">
		    	<img style="width: 100%; object-fit: cover;" src="{{ config('configuracion.url_instancia_cliente')."/storage/app/calificaciones/horarios_cursos".$curso->imagen }}" class="" />
		    </div>

		</div>
	</div>
	<br/><br/>

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