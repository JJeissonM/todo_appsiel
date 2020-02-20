<?php
	use App\Http\Controllers\Sistema\VistaController;
?>
@extends('layouts.principal')

@section('estilos_1')
	<style type="text/css">
		#div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:0;
		}
	</style>
@endsection

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<?php 
		//echo basename(__FILE__, '.php');
	?>
	
	
		<div class="container-fluid">
			<div class="marco_formulario">
			    <h4>Nuevo registro</h4>
			    <hr>

			    @if( isset($url_action) )
					{{ Form::open(['url'=>$url_action,'id'=>'form_create','files' => true]) }}
				@else
					{{ Form::open(['url'=>'web','id'=>'form_create','files' => true]) }}
				@endif
					<?php
					  if (count($form_create['campos'])>0) {
					  	$url = htmlspecialchars($_SERVER['HTTP_REFERER']);
					  	echo '<div class="row" style="margin: 5px;">'.Form::bsButtonsForm($url).'</div>';
					  }else{
					  	echo "<p>El modelo no tiene campos asociados.</p>";
					  }

					  //echo base_path();
					?>

					{{ VistaController::campos_dos_colummnas($form_create['campos']) }}

					{{ Form::hidden('url_id',Input::get('id'))}}
					{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
					
				{{ Form::close() }}
			</div>
		</div>
		<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){

			CKEDITOR.replace('contenido', {
		      height: 200,
		      // By default, some basic text styles buttons are removed in the Standard preset.
		      // The code below resets the default config.removeButtons setting.
		      removeButtons: ''
		    });


		});
	</script>
@endsection
