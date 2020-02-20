@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container col-sm-8 col-sm-offset-2">
		
		<br/><br/><br/>

		<div class="row">
			<div class="col-sm-4" align="center">
				<a href="{{url('web/?id='.Input::get('id').'&id_modelo=39')}}">
          			<img class="img-responsive" src="{{ asset('assets/img/propiedad_horizontal/propiedades.png') }}" width="80px" height="80px" />
	          		<br/>
	                Inmuebles
	            </a>
			</div>

			<?php
				$id_modelo = 43; // Documentos de CxC
				$id_transaccion = 5; // Generar CxC
			?>
			<div class="col-sm-4" align="center">
				<a href="{{url('propiedad_horizontal/generar_cxc?id='.Input::get('id').'&id_modelo='.$id_modelo.'&id_transaccion='.$id_transaccion)}}">
          			<img class="img-responsive" src="{{asset('assets/img/propiedad_horizontal/generar_cxc.png')}}" width="80px" height="80px" />
	          		<br/>
	                Generación CxC
	            </a>
			</div>

			<?php
				$id_modelo = 60; // Micrositios
			?>
			<div class="col-sm-4" align="center">
				<a href="{{url('web/?id='.Input::get('id').'&id_modelo='.$id_modelo)}}">
          			<img class="img-responsive" src="{{asset('assets/img/propiedad_horizontal/micrositio.png')}}" width="80px" height="80px" />
	          		<br/>
	                Micrositios
	            </a>
	        
			</div>
		</div>

		<br/>

		<div class="row">
			<div class="col-sm-4" align="center">
				<!-- <a href="{{url('propiedad_horizontal/actividades_escolares?id='.Input::get('id'))}}">
          			<img class="img-responsive" src="{{asset('assets/img/propiedad_horizontal/homeworks.png')}}" width="80px" height="80px" />
	          		<br/>
	                Reportes
	            </a>
	        -->
			</div>
		</div>
	</div>
@endsection