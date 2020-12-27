@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')
	
	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Modificando el registro</h4>
		    <hr>

			{{ Form::model($registro, [ 'url' => [ 'cxc_guardar_doc_encabezado/'.$registro->id ], 'method' => 'PUT','files' => true ]) }}
				
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


			{{ Form::close() }}
		</div>
	</div>
	<br/><br/>

	<div id="div_cargando">Cargando...</div>
@endsection