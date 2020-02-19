<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Modificando el registro</h4>
	    <hr>

		{{ Form::model($registro, ['url' => [$route_update.'/'.$registro->id], 'method' => 'PUT', 'id' => 'form_create']) }}

			<?php
			  if (count($form_create['campos'])>0) {
			  	if (isset($url_cancelar)) {
					$url = $url_cancelar;
				}else{
					$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				}
			  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
			  }else{
			  	echo "<p>El modelo no tiene campos asociados.</p>";
			  }

			?>

			{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

			<br/>

			{{ Form::hidden('id_app',Input::get('id'))}}
			{{ Form::hidden('modelo_id',Input::get('modelo_id'))}}


		{{ Form::close() }}
	</div>
</div>
<br/><br/>