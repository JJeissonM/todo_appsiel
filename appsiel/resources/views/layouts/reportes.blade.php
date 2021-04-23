@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_2')
	<link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">
	<style>

		body{
			font-family: 'Gotham-Narrow-Medium';
			font-size: 14px;
			font-weight: normal;
		}
		.sidebar {
		  /*
		  top: 51px;
		  height: 100%;
		  position: fixed;
		  width: 200px;
		  z-index: 1;
		  left: 0;
		  padding: 10px;*/
		  background-color: #f5f5f5;
		  overflow-x: scroll;
		}

		.contenido {
			margin-left: 200px; /* Same as the width of the sidenav */
  			padding: 0px 10px;
		  	float: left;
		  	width: 100%;
		}
	</style>
	
@endsection

@section('content')
	
		{{ Form::bsMigaPan($miga_pan) }}
		<hr>

		<div class="container-fluid">

			<div class="row">
				
				<div class="col-md-2 sidebar">
					@yield('sidebar')
				</div>

				<div class="col-md-10">
					
					@include('layouts.mensajes')

					@yield('contenido')

				</div>

			</div>

		</div>

@endsection

@section('scripts')
	@yield('scripts_reporte')
@endsection