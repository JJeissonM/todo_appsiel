<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

{{ Form::open(['url'=>$form_create['url'],'id'=>'form_create','files' => true]) }}

	<?php
		$botones = "El modelo no tiene campos asociados.";
	  	if ( count($form_create['campos'])>0 ) {
	  		$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
	  		$botones = Form::bsButtonsForm($url);
	  	}
	?>

	<div class="row botones" style="margin: 5px;"> {{ $botones }} </div>

	{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

	{{ Form::hidden('url_id',Input::get('id')) }}
	{{ Form::hidden('url_id_modelo', Input::get('id_modelo')) }}
	{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}

	@yield('campos_adicionales')
	
{{ Form::close() }}