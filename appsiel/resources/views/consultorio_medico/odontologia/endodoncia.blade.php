<?php 
	$ID = "tab_" . $consulta->id . "_model_".$key;
	$modelo_entidad_endodoncia_id = 308;
	$modelo_entidad = App\Sistema\Modelo::find( $modelo_entidad_endodoncia_id );

	$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_endodoncia_id );
?>

<br><br>

<button class="btn btn-warning btn-xs btn_actualizar_modelo" data-url="{{ url( 'core/eav/' . $modelo_entidad_endodoncia_id . '/edit?id=' . Input::get('id') . '&id_modelo=' . $modelo_entidad_endodoncia_id . '&modelo_padre_id=' . $modelo_padre_id . '&registro_modelo_padre_id=' . $registro_modelo_padre_id . '&modelo_entidad_endodoncia_id=' . $modelo_entidad_endodoncia_id . '&modo_peticion=ajax' ) }}" title="Actualizar" data-panel_id="{{$ID}}"><i class="fa fa-btn fa-edit"></i></button>

@include( 'core.modelo_eav.form_eliminar_registros', [ 'id_app' => Input::get('id'), 'id_modelo' => Input::get('id_modelo'), 'modelo_padre_id' => $modelo_padre_id, 'registro_modelo_padre_id' => $registro_modelo_padre_id, 'modelo_entidad_id' => $modelo_entidad_endodoncia_id, 'lbl_descripcion_modelo_entidad' => $modelo_entidad->descripcion, 'ruta_redirect' => 'consultorio_medico/pacientes/'.$registro->id ] )

<div class="div_spin" style="width: 100%; display: none; text-align: center;">
    <img src="{{ asset( 'img/spinning-wheel.gif') }}" width="64px">
</div>

<div class="alert alert-success alert-dismissible fade in" style="display: none;" id="mensaje_alerta">
</div>

<div id="contenido_seccion_modelo_{{$ID}}" class="contenido_seccion_modelo">
	{!! $datos !!}
</div>