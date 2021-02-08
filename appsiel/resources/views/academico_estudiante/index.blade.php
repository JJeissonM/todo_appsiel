@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		/* Colorize-zoom Container */
		.img-hover-zoom--colorize img {
		  transition: transform .1s;
		}

		/* The Transformation */
		.img-hover-zoom--colorize:hover img {
		  transform: scale(1.4);
		}

		body{
			background-position: bottom;
			background-attachment: fixed;
			background-size: cover;
			background-image: url({{asset('assets/img/academico_estudiante/fondo-estudiante.png')}})			
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container col-sm-12">
		
		<br/><br/><br/>

		<div class="row">
			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{url('academico_estudiante/horario?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/horario.png')}}" width="80px" height="80px" />
	          		<br/>
	                Horario
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{url('academico_estudiante/calificaciones?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/calificaciones.png')}}" width="80px" height="80px" />
	          		<br/>
	                Calificaciones
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{url('academico_estudiante/observador_show/'.$estudiante->id)}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/observador.png')}}" width="80px" height="80px" />
	          		<br/>
	                Observador
	            </a>
			</div>
		</div>

		<br/>

		<div class="row">
			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{ url( 'academico_estudiante_aula_virtual/'.$curso->id.'?id='.Input::get('id') . '&fecha=' . date('Y-m-d') ) }}">
          			<img class="img-responsive" src="{{ asset('assets/img/academico_estudiante/aula_virtual.png')}}" width="80px" height="80px" />
	          		<br/>
	                Aula Virtual
	            </a>
			</div>
		<!-- -->

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{ url( 'mis_asignaturas/'.$curso->id.'?id='.Input::get('id') ) }}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/homeworks.png')}}" width="80px" height="80px" />
	          		<br/>
	                Mis Asignaturas
	            </a>
			</div>

			<?php 


				$libreta = App\Tesoreria\TesoLibretasPago::where( 'id_estudiante', $estudiante->id )->get()->last();

				if ( is_null($libreta ) )
				{
					$libreta_id = 0;
				}else{
					$libreta_id = $libreta->id;
				}
			?>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{ url('academico_estudiante/mi_plan_de_pagos/'.$libreta_id.'?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/libreta_pagos.png')}}" width="80px" height="80px" />
	          		<br/>
	                Libreta de pagos
	            </a>
			</div>
		</div>

		<br/>

		<div class="row">

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="https://gmail.com" target="_blank">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/correo_institucional.png')}}" width="80px" height="80px" />
	          		<br/>
	                Correo institucional
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a style="color: #000;" href="{{ url('academico_estudiante/reconocimientos?id=' . Input::get('id') ) }}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/reconocimientos.png')}}" width="80px" height="80px" />
	          		<br/>
	                Reconocimientos
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				&nbsp;
			</div>
		</div>
	</div>
@endsection