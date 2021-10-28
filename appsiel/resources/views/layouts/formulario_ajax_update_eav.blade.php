<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>{{ $title }}</h4>
	    <hr>

	    {{ Form::model($registro, [ 'url' => [$form_create['url']], 'method' => 'POST', 'id' => 'form_create', 'files' => true ] ) }}

			{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

			{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}

			{{ Form::hidden('modo_peticion', Input::get('modo_peticion')) }}

		{{ Form::close() }}
	    
	    @if( $buttons != 'no' )
	        <button class="btn btn-primary btn-sm btn_save_modal"> <i class="fa fa-save"></i> Guardar </button>
			
			<button class="btn btn-danger btn-sm btn_close_modal"> <i class="fa fa-close"></i> Cerrar </button>
		@endif
	</div>
</div>
<br/><br/>