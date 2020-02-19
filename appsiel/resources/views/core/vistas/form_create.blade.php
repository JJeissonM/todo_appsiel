<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

<div class="container-fluid">
	<div class="marco_formulario">
		@if( isset($form_create['subtitulo']) )
			<h4>{{ $form_create['subtitulo'] }}</h4>
		@else
	    	<h4>Nuevo registro</h4>
	    @endif
	    <hr>

		{{ Form::open( array('url'=>$form_create['url'], 'id' => 'form_create') ) }}
			
			<?php
				if (isset($url_cancelar)) {
					$url = $url_cancelar;
				}else{
					$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
				}
			?>
			{{ Form::bsButtonsForm($url)}}

			{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

			<br/>

			{{ Form::hidden('id',Input::get('id'))}}
			{{ Form::hidden('modelo_id',Input::get('id_modelo'))}}

		{{ Form::close() }}
	</div>
</div>
<br/><br/>