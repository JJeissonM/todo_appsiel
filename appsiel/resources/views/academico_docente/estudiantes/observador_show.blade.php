@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'matriculas/estudiantes/observador/imprimir_observador/'.$id ) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<br>
			<br>
			
			<?php
				echo $view_pdf;
			?>
			
		</div>
	</div>
	<br/><br/>	

@endsection