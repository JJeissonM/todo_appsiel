<?php
    use App\Http\Controllers\Sistema\VistaController;
?>

{{ Form::open([ 'url' => $form_create['url'], 'id'=>'form_store_nuevo_total_factura']) }}

    {{ VistaController::campos_una_colummna($form_create['campos']) }}

    <div class="container-fluid">
        <button class="btn btn-primary" id="btn_store_nuevo_total_factura"> <i class="fa fa-save"></i> Guardar </button>
    </div>

    <br>

	<input type="hidden" name="url_id_modelo" value="{{ Input::get('id_modelo') }}">
	<input type="hidden" name="url_id" value="20">
	<input type="hidden" name="url_id_transaccion" value="47">
	<input type="hidden" name="documento_id" value="{{ $documento_id }}">

{{ Form::close() }}