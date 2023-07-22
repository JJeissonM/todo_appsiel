@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'matriculas/estudiantes/observador/imprimir_observador/'.$id ) }}

	&nbsp;&nbsp;&nbsp;<a class="btn-gmail" href="{{ url('matriculas/estudiantes/gestionresponsables/estudiante_id') . '?id=1&id_modelo=29&estudiante_id=' . $id }}" title="Gestionar responsables del estudiante"><i class="fa fa-btn fa-users"></i></a>
	
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'matriculas/estudiantes/observador/valorar_aspectos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
	
	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'matriculas/estudiantes/observador/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'matriculas/estudiantes/observador/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
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