@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;
	<div class="btn-group">
		
		<a class="btn btn-primary btn-xs btn-detail" href="{{ url( $acciones->create ) }}" title="Crear"><i class="fa fa-btn fa-plus"></i>&nbsp;Crear</a>

		{{ Form::bsBtnEdit( str_replace('id_fila', $id, $acciones->edit ) ) }}
		
		{{ Form::bsBtnPrint( str_replace('id_fila', $id, $acciones->imprimir ) ) }}
		
		<a class="btn btn-success btn-xs btn-detail" href="{{ url( 'inscripciones_crear_matricula/' . $id .'?id='.Input::get('id').'&id_modelo=19' ) }}" title="Crear"><i class="fa fa-btn fa-check"></i>&nbsp; Matrícular </a>

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
	<br/><br/>	

@endsection