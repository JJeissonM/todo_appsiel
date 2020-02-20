@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<?php
		use App\Http\Controllers\Sistema\VistaController;
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


				{{ Form::hidden( 'lista_campos', null, ['id' => 'lista_campos'] ) }}

				{{ Form::hidden('url_id',Input::get('id'))}}
				{{ Form::hidden('url_id_modelo',Input::get('id_modelo'))}}
				
			{{ Form::close() }}

			<table class="table table-striped" id="tabla_lista_campos">
				<thead>
					<tr>
						<th>Etiqueta</th>
						<th>Tipo</th>
						<th>Acción</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
<br/><br/>


	<?php

		//print_r($form_create['campos']);
	?>



	<div id="div_cargando">Cargando...</div>
@endsection

@section('scripts')
	<script type="text/javascript">
		$(document).ready(function(){
			//$('#form_create :first-child').focus();
		});
	</script>
@endsection