<?php
	use App\Http\Controllers\Core\ModeloEavController;

	$id_modelo = 95; // Pacientes
	
    $modelo_padre_id = 96; // Consultas Médicas
    $registro_modelo_padre_id = $consulta->id;

    $ids_modelos_relacionados = [ 237, 238, 239, 240, 241, 286, 287, 288 ];

?>
<br>

<a class="btn btn-info" href="{{ url( 'salud_imprimir_historia_medica_ocupacional/' . $consulta->id ) }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i> Historia </a>
&nbsp;&nbsp;&nbsp;&nbsp;
<a class="btn btn-info" href="{{ url( 'salud_imprimir_certificado_aptitud/' . $consulta->id ) }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i> Certificado </a>

@foreach( $ids_modelos_relacionados AS $key => $value )
	<?php 
		$modelo_entidad_id = $value;
		$modelo_entidad = App\Sistema\Modelo::find( $value );

		$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );
	?>

	<h4>{{$modelo_entidad->descripcion}}</h4>
	<hr>

	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'core/eav/' . $modelo_entidad_id . '/edit?id=' . Input::get('id') . '&id_modelo=' . $modelo_entidad_id . '&modelo_padre_id=' . $modelo_padre_id . '&registro_modelo_padre_id=' . $registro_modelo_padre_id . '&modelo_entidad_id=' . $modelo_entidad_id ) }}

	@include( 'core.modelo_eav.form_eliminar_registros', [ 'id_app' => Input::get('id'), 'id_modelo' => Input::get('id_modelo'), 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id' => $modelo_entidad_id, 'lbl_descripcion_modelo_entidad' => $modelo_entidad->descripcion, 'ruta_redirect' => 'consultorio_medico/pacientes/'.$registro->id ] )

	<br><br>
	{!! $datos !!}

@endforeach
	<br>
	<br>