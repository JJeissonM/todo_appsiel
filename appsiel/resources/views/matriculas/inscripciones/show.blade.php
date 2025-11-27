@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;
	<div class="btn-group">
		
		<a class="btn-gmail" href="{{ url( $acciones->create ) }}" title="Nueva inscripción"><i class="fa fa-btn fa-plus"></i></a>

		{{ Form::bsBtnEdit( str_replace('id_fila', $id, $acciones->edit ) ) }}
		
		{{ Form::bsBtnPrint( str_replace('id_fila', $id, $acciones->imprimir ) ) }}
		
		<a class="btn-gmail" href="{{ url( 'inscripciones_crear_matricula/' . $id .'?id='.Input::get('id').'&id_modelo=' . Input::get('id_modelo') ) }}" title="Matrícular"><i class="fa fa-btn fa-book"></i></a>

	</div>

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'matriculas/inscripcion/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'matriculas/inscripcion/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif
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