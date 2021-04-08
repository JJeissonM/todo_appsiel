@extends('layouts.principal')

@section('estilos_1')

	<style type="text/css">

		.marco_formulario {
			background-position: bottom;
			background-attachment: fixed;
			background-size: cover;			
			background-image: url({{asset('assets/img/academico_estudiante/learning-4264032_1920.jpg')}})			
		}

		.hide-replaced.ws-inputreplace {
		    display: none !important;
		}
		.input-picker{
			overflow: visible;
		    font-size: 13px;
		    outline: 0;
		    text-align: center;
		    font-family: sans-serif;
		    width: 4.23077em;
		    min-width: 21.76923em !important;
		    max-width: 98vw;
		}
		.input-picker .picker-list td > button.othermonth {
		    color: #888888;
		    background: #fff;
		}
		.ws-inline-picker.ws-size-2, .ws-inline-picker.ws-size-4 {
		    width: 49.6154em;
		}
		.ws-size-4 .ws-index-0, .ws-size-4 .ws-index-1 {
		    border-bottom: 0.07692em solid #eee;
		    padding-bottom: 1em;
		    margin-bottom: 0.5em;
		}
		.picker-list.ws-index-2, .picker-list.ws-index-3 {
		    margin-top: 3.5em;
		}
		div.ws-invalid input {
		    border-color: #c88;
		}
		.ws-invalid label {
		    color: #933;
		}
		div.ws-success input {
		    border-color: #8c8;
		}
		form {
		    margin: 10px auto;
		    min-width: 20em;
		    padding: 10px;
		    background-color: #d2d2d2;
		    border-radius: 5px;
		}
		.form-row {
		    padding: 5px 10px;
		    margin: 5px 0;
		}
		label {
		    display: block;
		    margin: 3px 0;
		}
		.form-row input {
		    width: 120px;
		    padding: 3px 1px;
		    border: 1px solid #ccc;
		    box-shadow: none;
		}
		.form-row input[type="checkbox"] {
		    width: 15px;
		}
		.date-display {
		    display: inline-block;
		    min-width: 200px;
		    padding: 5px;
		    border: 1px solid #ccc;
		    min-height: 1em;
		}
		.show-inputbtns .input-buttons {
		    display: inline-block;
		}

		.div_programacion{
		    padding: 10px;
		    background-color: #d2d2d2;
		    border-radius: 5px;
		}
	</style>
@endsection

	@section('content')
	{{ Form::bsMigaPan($miga_pan) }}		
	<hr>

	@include('layouts.mensajes')


	<div class="container-fluid">
		<div class="marco_formulario">
		    <br>
		    <h3 class="text-info" style="width: 100%; text-align: left;">Curso :</b> {{ $curso->descripcion }}</h3>
		    <hr>
		    <div class="row">

		    	<div class="col-md-4">		    		
		    		<form action="#" class="ws-validate">
					    <h4 class="text-info" style="width: 100%; text-align: left; color: #9c27b0"> <small> <i class="fa fa-calendar"></i> </small> CALENDARIO </h4>
					    <span class="text-success"> Haga click en cualquier día para revisar el horario correspondiente. </span>
					    <hr>
					    <div class="form-row">
					        <input type="date" class="hide-replaced" id="fecha" />
					    </div>
					</form>
		    	</div>

		    	<div class="col-md-8">
					<div class="div_programacion">
						<h4 class="text-info" style="width: 100%; text-align: left; color: #9c27b0">Programación para el día <i> {{ $dia_semana }}, {{ explode('-', Input::get('fecha') )[2] }} de {{ nombre_mes( explode('-', Input::get('fecha') )[1] ) }} </i> </h4> 

						<ul class="list-group">
							
							@foreach( $eventos as $evento )
								<?php 
									$descripcion_evento = $evento->descripcion;
									if ( $evento->tipo_evento == 'clase_normal' )
									{
										$descripcion_evento = 'No Asignatura';
										if ( !is_null( $evento->asignatura ) )
										{
											$descripcion_evento = $evento->asignatura->descripcion;
										}
											
									}
								?>

								@if( $evento->tipo_evento == 'descanso' )
									<li class="list-group-item">
										<h4 style="display: inline; font-family: Comic Sans MS, Comic Sans, cursive; italic;"><small><i class="fa fa-clock-o"></i> {{ Carbon\Carbon::parse($evento->hora_inicio)->format('g:i A' ) }}</small> {!! $descripcion_evento !!} </h4>
										<table class="table">
											<tbody>
												<tr>
													<td> 
														<h4> 
															@for( $i= 0; $i < 7; $i++ )
																<i class="fa fa-{{ $evento->fa_icon}}"> </i> &nbsp;&nbsp;&nbsp;&nbsp;
															@endfor
														</h4>
													</td>
												</tr>
											</tbody>
										</table>
									</li>

									<?php continue; ?>

								@endif
								
								<li class="list-group-item">
									<h4 style="display: inline; font-family: Comic Sans MS, Comic Sans, cursive; italic;"><small><i class="fa fa-clock-o"></i> {{ Carbon\Carbon::parse($evento->hora_inicio)->format('g:i A' ) }}</small> {!! $descripcion_evento !!} </h4>
									<table class="table">
										<tbody>
											<tr>
												<td>
													@if( $evento->enlace_reunion_virtual != '' )
														<a style="cursor: pointer; font-size: 16px;" href="{{ $evento->enlace_reunion_virtual }}" target="_blank"> <i class="fa fa-link"></i>  Enlace reunión virtual </a>
													@else
														--
													@endif
												</td>
												<td>
													@if( $evento->guia_academica_id != 0 )
														<a style="cursor: pointer; font-size: 16px;" href="{{ url('academico_estudiante/ver_guia_plan_clases/' . $evento->curso_id . '/' . $evento->asignatura_id . '/' . $evento->guia_academica_id . '?id=6' )}}" target="_blank"> <i class="fa fa-file-pdf-o"></i> Guia Académica </a>
													@else
														--
													@endif 
												</td>
												<td>
													@if( $evento->actividad_escolar_id != 0 )
														<a style="cursor: pointer; font-size: 16px;" href="{{ url( 'actividades_escolares/hacer_actividad/' . $evento->actividad_escolar_id . '?id=6') }}" target="_blank"> <i class="fa fa-flask"></i> Actividad escolar </a>
													@else
														--
													@endif
												</td>
											</tr>
										</tbody>
									</table>
								</li>
							@endforeach
						</ul>
					</div>
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
	
	<script src="{{asset('assets/js/js-webshim/minified/polyfiller.js')}}"></script>

	<script type="text/javascript">
		webshim.setOptions('forms-ext', {
		    replaceUI: 'auto',
		    types: 'date',
		    date: {
		        startView: 2,
		        inlinePicker: true,
		        classes: 'hide-inputbtns'
		    }
		});
		webshim.setOptions('forms', {
		    lazyCustomMessages: true
		});
		//start polyfilling
		webshim.polyfill('forms forms-ext');

		//only last example using format display
		$(function () {
		    $('.format-date').each(function () {
		        var $display = $('.date-display', this);
		        $(this).on('change', function (e) {
		            //webshim.format will automatically format date to according to webshim.activeLang or the browsers locale
		            var localizedDate = webshim.format.date($.prop(e.target, 'value'));
		            $display.html(localizedDate);
		        });
		    });
		});

		$(document).ready(function(){

			$('#fecha').on('change',function(){
				console.log( $(this).val() );
				var id = getParameterByName('id');
				window.location.assign( '../academico_estudiante_aula_virtual/' + "{{$curso->id}}" + '?id=' + id + '&fecha=' + $(this).val() );
			});

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}

		});
	</script>
@endsection