@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
	<div class="col-md-8 col-md-offset-2" style="background-color: white;border: 1px solid #d9d7d7;box-shadow: 5px 5px gray;">
	    <h4 style="color: gray;">Creación de un nuevo registro</h4>
	    <hr>
		 @include($ruta_modelo.'.create')
		</div>
	</div>
@endsection