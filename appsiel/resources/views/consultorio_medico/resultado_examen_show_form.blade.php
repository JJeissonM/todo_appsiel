{{ Form::open( [ 'url' => 'consultorio_medico/eliminar_resultado_examen_medico', 'style' => 'display:inline;' ] ) }}
	{{ Form::hidden('paciente_id2', $paciente_id) }}
	{{ Form::hidden('consulta_id2', $consulta_id) }}
	{{ Form::hidden('examen_id2', $examen_id) }}
	{{ Form::hidden( 'ruta_redirect', 'consultorio_medico/pacientes/'.$paciente_id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}		

	{{ Form::hidden( 'lbl_descripcion_modelo_entidad', 'Resultados de exámen' ) }}

	<button class="btn btn-danger btn-xs btn-detail btn_eliminar" title="Eliminar"> <i class="fa fa-trash"></i> &nbsp; </button>
{{ Form::close() }}


{{ Form::open( ['url' => ['consultorio_medico/resultado_examen_medico/actualiza'], 'method' => 'PUT', 'id' => 'form_resultados_examenes' ] ) }}
	{{ Form::hidden('paciente_id', $paciente_id) }}
	{{ Form::hidden('consulta_id', $consulta_id) }}
	{{ Form::hidden('examen_id', $examen_id) }}

	@include('consultorio_medico.resultado_examen_show_tabla')

{{ Form::close() }}