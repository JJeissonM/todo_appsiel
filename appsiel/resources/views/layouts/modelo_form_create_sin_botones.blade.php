<?php
	use App\Http\Controllers\Sistema\VistaController;

	$form_id = 'form_create';

	if ( isset($form_create['form_id']) ) {
		$form_id = $form_create['form_id'];
	}
?>

<div class="container-fluid">
	<div class="marco_formulario">
		{{ Form::open(['url'=>$form_create['url'],'id'=> $form_id,'files' => true]) }}

			@if( isset( $datos_columnas ) )
				@if( $datos_columnas )
					{{ VistaController::campos_dos_colummnas($form_create['campos']) }}
				@else
					{{ VistaController::campos_una_colummna($form_create['campos']) }}
				@endif
			@else
				{{ VistaController::campos_una_colummna($form_create['campos']) }}
			@endif
			
		{{ Form::close() }}
	</div>
</div>

@if( isset($archivo_js) )
	<script src="{{ asset( $archivo_js ) }}"></script>
@endif
