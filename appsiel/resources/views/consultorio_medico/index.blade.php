@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	{!! $select_crear !!}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
		</div>
	</div>

	<br/>
@endsection