<?php
	use App\Http\Controllers\Core\ModeloEavController;
	
    $modelo_padre_id = 96; // Consultas Médicas
    $registro_modelo_padre_id = $consulta->id;
	$modelo_entidad_id = 110; // Anamnesis

	$modelo_entidad = App\Sistema\Modelo::find( $modelo_entidad_id );
	
	$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

?>
<br>

<h4>{{$modelo_entidad->descripcion}}</h4>
<hr>

@can('salud_anamnesis_edit')
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'core/eav/' . $modelo_entidad_id . '/edit?id=' . Input::get('id') . '&id_modelo=' . $modelo_entidad_id . '&modelo_padre_id=' . $modelo_padre_id . '&registro_modelo_padre_id=' . $registro_modelo_padre_id . '&modelo_entidad_id=' . $modelo_entidad_id ) }}
@endcan

@include( 'core.modelo_eav.form_eliminar_registros', [ 'id_app' => Input::get('id'), 'id_modelo' => Input::get('id_modelo'), 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id' => $modelo_entidad_id, 'lbl_descripcion_modelo_entidad' => $modelo_entidad->descripcion, 'ruta_redirect' => 'consultorio_medico/pacientes/'.$registro->id ] )

<br><br>
{!! $datos !!}