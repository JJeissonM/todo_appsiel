<div class="row">
	<div class="col-md-6 botones-gmail">
		@can('salud_consultas_edit')
			<button class="btn-gmail btn_edit_registro_datos_consulta" title="Modificar">
				<i class="fa fa-btn fa-edit"></i>
                <span data-consulta_id="{{ $consulta->id }}"></span>
            </button>
		@endcan
		@can('salud_consultas_delete')
			<button class="btn-gmail btn_delete_registro_datos_consulta" title="Modificar" data-url="{{ url( 'consultorio_medico/consultas/' . $consulta->id . '/delete?id=' . Input::get('id') . '&id_modelo=' . $modelo_consultas->id . '&paciente_id=' . $id . '&modelo_pacientes_id=' . Input::get('id_modelo') ) }}">
				<i class="fa fa-trash"></i>
                <span data-consulta_id="{{ $consulta->id }}"></span>
            </button>
		@endcan
		@can('salud_consultas_print')
			{{ Form::bsBtnPrint( 'consultorio_medico/consultas/'.$consulta->id.'/print?paciente_id='.$id ) }}
		@endcan
	</div>
	<!-- <div class="col-md-6">
		<div class="row">
			<div class="col-md-6">
				Imprimir: { { Form::select( 'formato_impresion_id', [ 'datos_consulta' => 'Datos consulta', 'historial_completo' => 'Historial completo' ], null, [ 'id' =>'formato_impresion_id', 'class' =>'form-control' ]) }}
			</div>
			<div class="col-md-6">
				@ can('salud_consultas_print')
					{ { Form::bsBtnPrint( 'consultorio_medico/consultas/'.$consulta->id.'/print?paciente_id='.$id ) }}
				@ endcan
			</div>
		</div>
	</div>
-->
</div>