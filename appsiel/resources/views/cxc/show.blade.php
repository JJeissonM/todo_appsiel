@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'cxc/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'cxc_modificar_doc_encabezado/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'cxc_print/'.$id ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEmail( 'cxc_enviar_por_email/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'cxc/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'cxc/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		@endif
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<?php
				echo $view_pdf;

				echo "<br/><br/><br/>";

				echo $tabla2;
			?>
			
		</div>
	</div>
	<br/><br/>	

@endsection