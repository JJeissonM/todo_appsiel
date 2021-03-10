
{{ Form::open( [ 'url' => 'core/eliminar_registros_eav?id='.$id_app.'&id_modelo='.$id_modelo, 'style' => 'display:inline;', 'class' => 'form_eliminar' ] ) }}
	{{ Form::hidden( 'modelo_padre_id', $modelo_padre_id ) }}
	{{ Form::hidden( 'registro_modelo_padre_id', $registro_modelo_padre_id ) }}
	{{ Form::hidden( 'modelo_entidad_id', $modelo_entidad_id ) }}
	{{ Form::hidden( 'lbl_descripcion_modelo_entidad', $lbl_descripcion_modelo_entidad ) }}
	{{ Form::hidden( 'ruta_redirect', $ruta_redirect.'?id='.$id_app.'&id_modelo='.$id_modelo ) }}
	
	<button class="btn btn-danger btn-xs btn_eliminar_datos_modelo" title="Eliminar" data-descripcion_modelo="{{ $lbl_descripcion_modelo_entidad }}"> <i class="fa fa-trash"></i></button>
{{ Form::close() }}