@extends('layouts.academico_estudiante')

@section('content')

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