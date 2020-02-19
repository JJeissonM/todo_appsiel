@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Resultados de la importación</h4>
		    <hr>

			<div class="alert alert-success">
				<strong>Conciliación</strong>
				<br/><br/>

				{!! $vista !!}
			</div>

		</div>
	</div>
<br/><br/>
@endsection