@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('estilos_2')
	<style>
		.sidebar {
		  /*
		  top: 51px;*/
		  width: 200px;
		  z-index: 1;
		  height: 100%;
		  position: fixed;
		  left: 0;
		  background-color: #f5f5f5;
		  overflow-x: hidden;
		  padding: 10px;
		}

		.contenido {
			margin-left: 200px; /* Same as the width of the sidenav */
  			padding: 0px 10px;
		}
	</style>
@endsection

@section('content')
	
		{{ Form::bsMigaPan($miga_pan) }}
		<hr>


		<div class="sidebar">
			@yield('sidebar')	
		</div>

		<div class="contenido">
			
			@include('layouts.mensajes')

			@yield('contenido')	
		</div>
		

@endsection

@section('scripts')
	@yield('scripts_reporte')
@endsection