@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	
	&nbsp;&nbsp;&nbsp;
	<div class="btn-group">
		@if( isset($url_crear) )
			@if($url_crear!='')
				{{ Form::bsBtnCreate($url_crear) }}
			@endif
		@endif

		@if( isset($url_edit) )
			@if($url_edit!='')
				{{ Form::bsBtnEdit2(str_replace('id_fila', $registro->id, $url_edit),'Editar') }}
			@endif
		@endif
	</div>

	<div class="pull-right">
		@if($reg_anterior!='')
			{{ Form::bsBtnPrev('vtas_clientes/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif

		@if($reg_siguiente!='')
			{{ Form::bsBtnNext('vtas_clientes/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
		@endif
	</div>

	<hr>

	@include('layouts.mensajes')

	@include('layouts.form_show',compact('form_create','url_edit','tabla'))

@endsection