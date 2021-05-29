<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
		{{ Form::model($registro, [ 'url' => [$form_create['url']], 'method' => 'PUT', 'id' => 'form_create', 'files' => true ] ) }}

			{{ VistaController::campos_una_colummna($form_create['campos']) }}
			
		{{ Form::close() }}
	</div>
</div>
<br/>

@if( isset($archivo_js) )
	<script src="{{ asset( $archivo_js ) }}"></script>
@endif
