@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'contabilidad_print/'.$id ) }}
	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'contabilidad/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'contabilidad/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
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