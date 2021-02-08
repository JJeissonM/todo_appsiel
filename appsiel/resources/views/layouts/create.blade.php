@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Nuevo registro</h4>
		    <hr>

		    @include('layouts.form_create',compact('form_create'))

			@if(isset($tabla))

				{!! $tabla !!}
				
				<br/><br/>

			@endif
		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			$('#fecha').val( get_fecha_hoy() );

			$('#bs_boton_guardar').on('click',function(event){
				event.preventDefault();

				if ( !validar_requeridos() )
				{
					return false;
				}

				// Desactivar el click del botón
				$( this ).off( event );

				$('#form_create').submit();
			});
		});
	</script>
	@if( isset($archivo_js) )
		<script src="{{ asset( $archivo_js ) }}"></script>
	@endif
@endsection