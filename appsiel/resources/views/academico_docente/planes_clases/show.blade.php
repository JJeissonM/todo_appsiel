<?php  
    $variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo');
?>

@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'sga_planes_clases_imprimir/'.$id ) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'web/'.$id.'/edit'.$variables_url ) }}

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev( 'sga_planes_clases/'.$reg_anterior.$variables_url ) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext( 'sga_planes_clases/'.$reg_siguiente.$variables_url ) }}
		@endif
	</div>
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			{!! $vista !!}
			
		</div>
	</div>
	<br/><br/>	

@endsection