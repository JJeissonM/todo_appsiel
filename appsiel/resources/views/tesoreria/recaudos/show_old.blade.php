@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'tesoreria/recaudos/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'tesoreria/recaudos_print/'.$id ) }}
	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'tesoreria/recaudos/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'tesoreria/recaudos/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
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