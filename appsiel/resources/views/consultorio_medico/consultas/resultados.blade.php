<?php
	use App\Http\Controllers\Core\ModeloEavController;
	
    $modelo_padre_id = 96; // Consultas Médicas
    $registro_modelo_padre_id = $consulta->id;
	$modelo_entidad_id = 111; // Resultados de la consulta

	$datos = ModeloEavController::show_datos_entidad( $modelo_padre_id, $registro_modelo_padre_id, $modelo_entidad_id );

?>
<br>
@if( $datos == '' )
	@can('salud_consultas_create')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'core/eav/create?id='.Input::get('id').'&id_modelo='.$modelo_entidad_id.'&modelo_padre_id='.$modelo_padre_id.'&registro_modelo_padre_id='.$registro_modelo_padre_id.'&modelo_entidad_id='.$modelo_entidad_id ) }}
	@endcan
@else
	@can('salud_consultas_edit')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'core/eav/'.$modelo_entidad_id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_entidad_id.'&modelo_padre_id='.$modelo_padre_id.'&registro_modelo_padre_id='.$registro_modelo_padre_id.'&modelo_entidad_id='.$modelo_entidad_id ) }}
	@endcan

	{{ Form::open( [ 'url' => 'core/eliminar_registros_eav?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'style' => 'display:inline;' ] ) }}
		{{ Form::hidden( 'modelo_padre_id', $modelo_padre_id ) }}
		{{ Form::hidden( 'registro_modelo_padre_id', $registro_modelo_padre_id ) }}
		{{ Form::hidden( 'modelo_entidad_id', $modelo_entidad_id ) }}
		{{ Form::hidden( 'lbl_descripcion_modelo_entidad', 'Resultado de la consulta' ) }}
		{{ Form::hidden( 'ruta_redirect', 'consultorio_medico/pacientes/'.$registro->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		
		<button class="btn btn-danger btn-xs btn-detail btn_eliminar" title="Eliminar"> <i class="fa fa-trash"></i> &nbsp; </button>
	{{ Form::close() }}

	<br>

	{!! $datos !!}

@endif