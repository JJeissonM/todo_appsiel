<div class="row">
	<div class="col-md-12 botones-gmail">
		@can('salud_consultas_edit')
			{{ Form::bsBtnEdit( 'consultorio_medico/consultas/'.$consulta->id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$id . '&action=edit' ) }}
		@endcan
		@can('salud_consultas_print')
			{{ Form::bsBtnPrint( 'consultorio_medico/consultas/'.$consulta->id.'/print?paciente_id='.$id ) }}
		@endcan
		@can('salud_consultas_delete')
			{{ Form::bsBtnEliminar( 'consultorio_medico/consultas/'.$consulta->id.'/delete?id='.Input::get('id').'&id_modelo='.$modelo_consultas->id.'&paciente_id='.$id.'&modelo_pacientes_id='.Input::get('id_modelo') ) }}
		@endcan
	</div>
</div>