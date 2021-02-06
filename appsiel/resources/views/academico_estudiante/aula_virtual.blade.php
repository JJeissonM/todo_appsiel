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
		    <br>
		    <h3>Curso :</b> {{ $curso->descripcion }}</h3>
		    <hr>
		    <div style="margin:20px;">
		    	<h3>Clases programadas para hoy {{ date('d') }} de {{ nombre_mes( date('m') ) }} </h3>


					<div class="well">
						<ul style="list-style: none;">
							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 8:00 a.m.</small> L. Castellana </h4>								<table class="table">
									<tbody>
										<tr>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="https://meet.google.com/qfi-hpcf-adw" target="_blank"> <i class="fa fa-link"></i>  Enlace meet </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="http://demo.appsiel.com.co/academico_estudiante/ver_guia_plan_clases/24/2/3806?id=6" target="_blank"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="http://demo.appsiel.com.co/actividades_escolares/hacer_actividad/4?id=6" target="_blank"> <i class="fa fa-flask"></i> Actividad escolar </a> 
											</td>
										</tr>
									</tbody>
								</table>
							</li>

							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 9:20 a.m.</small> Science </h4>								<table class="table">
									<tbody>
										<tr>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="https://meet.google.com/dvu-qdif-dav" target="_blank"> <i class="fa fa-link"></i>  Enlace meet </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="http://demo.appsiel.com.co/academico_estudiante/ver_guia_plan_clases/24/2/6873?id=6" target="_blank"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;" href="http://demo.appsiel.com.co/actividades_escolares/hacer_actividad/3481?id=6" target="_blank"> <i class="fa fa-flask"></i> Actividad escolar </a> 
											</td>
										</tr>
									</tbody>
								</table>
							</li>

							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 10:00 a.m.</small> Break </h4>								<table class="table">
									<tbody>
										<tr>
											<td> 
												<h4><i class="fa fa-futbol-o"></i>  <i class="fa fa-gamepad"></i></h4>
											</td>
										</tr>
									</tbody>
								</table>
							</li>

							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 10:20 a.m.</small> Informática </h4>								<table class="table">
									<tbody>
										<tr>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-link"></i>  Enlace meet </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-flask"></i> Actividad escolar </a> 
											</td>
										</tr>
									</tbody>
								</table>
							</li>


							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 11:00 a.m.</small> Ed. Artística </h4>
								<table class="table">
									<tbody>
										<tr>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-link"></i>  Enlace meet </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-flask"></i> Actividad escolar </a> 
											</td>
										</tr>
									</tbody>
								</table>
							</li>

							<li>
								<h4 style="display: inline;"><small><i class="fa fa-clock-o"></i> 11:00 a.m.</small> Cat. de la Paz y Ética </h4>			
								<table class="table">
									<tbody>
										<tr>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-link"></i>  Enlace meet </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a> 
											</td>
											<td> 
												<a style="cursor: pointer; font-size: 16px;"> <i class="fa fa-flask"></i> Actividad escolar </a> 
											</td>
										</tr>
									</tbody>
								</table>
							</li>

						</ul>
					</div>

		    </div>

		</div>
	</div>
	<br/><br/>

@endsection


<?php
    function nombre_mes($num_mes){
        switch($num_mes){
            case '01':
                $mes="Enero";
                break;
            case '02':
                $mes="Febrero";
                break;
            case '03':
                $mes="Marzo";
                break;
            case '04':
                $mes="Abril";
                break;
            case '05':
                $mes="Mayo";
                break;
            case '06':
                $mes="Junio";
                break;
            case '07':
                $mes="Julio";
                break;
            case '08':
                $mes="Agosto";
                break;
            case '09':
                $mes="Septiembre";
                break;
            case '10':
                $mes="Octubre";
                break;
            case '11':
                $mes="Noviembre";
                break;
            case '12':
                $mes="Diciembre";
                break;
            default:
                $mes="----------";
                break;
        }
        return $mes;
    }
?>

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