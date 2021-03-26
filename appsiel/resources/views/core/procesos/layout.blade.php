@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			<div class="container-fluid">
	    		<br><br>

	    		<div class="container-fluid">

					@yield('seccion_encabezado')

				</div>

	    		<div class="well">
					<h4 style="text-align: center; width: 100%;"> @yield('titulo') </h4>
					
					@yield('detalles')

				</div>

	    		<div class="row">
	    			<div class="col-md-12">

	    				@yield('formulario')

	    			</div>
	    		</div>

	    		<div class="row">
	    			<div class="col-md-12">

	    				<div class="row" style="padding:5px;" id="div_resultado">

						</div>

	    			</div>   				
	    		</div>
	    		

				{{ Form::Spin('128') }}

				<div class="row" id="mensaje_ok">
						
				</div>

			</div>
	    </div>
	</div>
	<br/><br/>
@endsection

@section('scripts')
	@yield('javascripts')
@endsection