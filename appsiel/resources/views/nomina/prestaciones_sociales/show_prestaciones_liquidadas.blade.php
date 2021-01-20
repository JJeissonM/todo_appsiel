@extends('layouts.principal')

@section('content')

	{{ Form::bsMigaPan($miga_pan) }}
	

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			<div class="container-fluid">
				@include('nomina.incluir.encabezado_transaccion',['encabezado_doc' => $documento_nomina, 'empresa' => $documento_nomina->empresa , 'descripcion_transaccion' => $documento_nomina->tipo_documento_app->descripcion ] )

				{!! $vista !!}
			</div>
		</div>
	</div>

@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

		});
	</script>
@endsection