@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">

			{!! $tabla !!}

		</div>
	</div>
	<br/><br/>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function()
		{
			$('.combobox2').change(function()
			{

				var cuenta_id = $(this).attr('id');

				document.getElementById( 'span_'+cuenta_id ).innerHTML = "";

				$('#div_cargando').show();
						
				var url = 'reasignar_grupos_cuentas_save/' + cuenta_id + '/' + $(this).val();

				$.get( url, function( respuesta ) {
			        $('#div_cargando').hide();

			        document.getElementById( 'span_'+cuenta_id ).innerHTML = respuesta;

			    });
			});
		});

		
	</script>
@endsection