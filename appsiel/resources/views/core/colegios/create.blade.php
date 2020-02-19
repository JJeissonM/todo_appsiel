<?php
	use App\Http\Controllers\Sistema\VistaController;
	$form_create = [
					'titulo' => 'Creación nuevo colegio',
					'subtitulo' => '',
					'url' => '/core/colegios',
					'campos' => [
									[
										'tipo' => 'select',
										'descripcion' => 'Empresa',
										'name' => 'id_empresa',
										'opciones' => $empresas,
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Descripción colegio',
										'name' => 'descripcion',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Slogan',
										'name' => 'slogan',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Resolución',
										'name' => 'resolucion',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Dirección',
										'name' => 'direccion',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Teléfono',
										'name' => 'telefonos',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Ciudad',
										'name' => 'ciudad',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Pie de firma #1',
										'name' => 'piefirma1',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'bsText',
										'descripcion' => 'Pie de firma #2',
										'name' => 'piefirma2',
										'value' => null,
										'atributos' => []
									],
									[
										'tipo' => 'select',
										'descripcion' => 'Maneja puesto',
										'name' => 'maneja_puesto',
										'opciones' => ['N'=>'No','S'=>'Si'],
										'value' => null,
										'atributos' => []
									]
								]
				];
?>

<div class="row" style="padding:5px;">
	{{ Form::bsButtonsForm('core/colegios?'.Input::get('id'))}}
</div>

<?php
	$i=2;
	foreach ($form_create['campos'] as $campo) {
		if($i%2==0){
			echo '<div class="row">';
			echo '<div class="col-md-6">';
		}else{
			echo '<div class="col-md-6">';
		}
			echo '<div class="row" style="padding:5px;">'.VistaController::dibujar_campo($campo).'</div>';
			echo '</div>';
		if($i%2==0){
			echo '</div>';
		}
		$i++;
	}
?>

{{ Form::hidden('id_app',Input::get('id'))}}