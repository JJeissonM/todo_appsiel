<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Modificando el registro</h4>
	    <hr>

		@if( !isset($url_action) )
	    	@php $url_action = 'web/'.$registro->id; @endphp
	    @endif

		{{ Form::model($registro, ['url' => [$url_action], 'method' => 'PUT','files' => true]) }}
		
			
			<?php
			  if (count($form_create['campos'])>0) {
			  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
			  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
			  }else{
			  	echo "<p>El modelo no tiene campos asociados.</p>";
			  }

			?>

			{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

			{{ Form::hidden('url_id',Input::get('id'))}}
			{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
			{{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
			{{ Form::hidden('datos_registro', $registro,['id'=>'datos_registro'])}}


		{{ Form::close() }}
	</div>
</div>
<br/><br/>