@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'sga_planes_clases_imprimir/'.$plan_id ) }}

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			{!! $vista !!}
			
		</div>
	</div>
	<br/><br/>	

@endsection