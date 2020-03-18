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
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container col-sm-8 col-sm-offset-2">
		
		<br/><br/><br/>

		<div class="row">
			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a href="{{url('academico_estudiante/horario?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/horario.png')}}" width="80px" height="80px" />
	          		<br/>
	                Horario
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a href="{{url('academico_estudiante/calificaciones?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/calificaciones.png')}}" width="80px" height="80px" />
	          		<br/>
	                Calificaciones
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a href="{{url('academico_estudiante/observador_show/'.$estudiante->id)}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/observador.png')}}" width="80px" height="80px" />
	          		<br/>
	                Observador
	            </a>
			</div>
		</div>

		<br/>

		<div class="row">
			<!-- <div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a href="{ {url('academico_estudiante/actividades_escolares?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{ {asset('assets/img/academico_estudiante/homeworks.png')}}" width="80px" height="80px" />
	          		<br/>
	                Actividades Escolares
	            </a>
			</div>
		-->

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				<a href="{{ url( 'mis_asignaturas/'.$curso->id.'?id='.Input::get('id') ) }}">
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
				<a href="{{ url('academico_estudiante/mi_plan_de_pagos/'.$libreta_id.'?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/academico_estudiante/libreta_pagos.png')}}" width="80px" height="80px" />
	          		<br/>
	                Libreta de pagos
	            </a>
			</div>

			<div class="col-sm-4 img-hover-zoom img-hover-zoom--colorize" align="center">
				&nbsp;
				<!-- <a href="{ { url( 'ver_foros/'.$curso->id.'?id='.Input::get('id') ) }}">
          			<img class="img-responsive" src="{ {asset('assets/img/academico_estudiante/foros.png')}}" width="80px" height="80px" />
	          		<br/>
	                Foros de discusión
	            </a>
	        -->
			</div>
		</div>
	</div>
@endsection