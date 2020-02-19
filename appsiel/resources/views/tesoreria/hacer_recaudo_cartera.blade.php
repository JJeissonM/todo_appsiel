@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	<div class="container-fluid">
		<div class="marco_formulario">

			<?php
				$nombre_completo=$estudiante->nombres." ".$estudiante->apellido1." ".$estudiante->apellido2;
			?>
			<h3>{{ $nombre_completo }}</h3>
			<h4>Matrícula: {{ $codigo_matricula }} /  Curso: {{ $curso->descripcion }}</h4>
		    <hr>

			@include('tesoreria.form_hacer_recaudo_cartera')
			
		</div>
	</div>
@endsection