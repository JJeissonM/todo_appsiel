@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'cancelacion_anticipo_print/'.$id ) }}

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'cancelacion_anticipo/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'cancelacion_anticipo/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
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