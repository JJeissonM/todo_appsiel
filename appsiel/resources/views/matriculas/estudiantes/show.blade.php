@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	

	<div class="row">
		<div class="col-md-6">
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
				@if(isset($botones))
					@php
						$i=0;
					@endphp
					@foreach($botones as $boton)
						{!! str_replace( 'id_fila', $registro->id, $boton->dibujar() ) !!}
						@php
							$i++;
						@endphp
					@endforeach
				@endif
			</div>
		</div>

		<div class="col-md-6">
			<div class="btn-group pull-right">
				@if($reg_anterior!='')
					{{ Form::bsBtnPrev('matriculas/estudiantes/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif

				@if($reg_siguiente!='')
					{{ Form::bsBtnNext('matriculas/estudiantes/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo')) }}
				@endif
			</div>
		</div>
	</div>
	<hr>

	@include('layouts.mensajes')

	@include('matriculas.estudiantes.datos_basicos')

@endsection