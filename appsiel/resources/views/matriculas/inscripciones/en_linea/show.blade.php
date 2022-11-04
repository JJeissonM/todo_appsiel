@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;
	<div class="btn-group">
        
		<a class="btn-gmail" href="{{ url( 'inscripciones_en_linea/create' ) }}" title="Crear Nueva"><i class="fa fa-btn fa-plus"></i></a>
        
		<a class="btn-gmail" href="{{ url( 'inscripciones_en_linea/print?id=' . $id ) }}" title="Imprimir" target="_blank"><i class="fa fa-btn fa-print"></i></a>		

	</div>

	<div class="pull-right">
        &nbsp;
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<?php
				echo $view_pdf;
			?>

		</div>
	</div>

@endsection