{{ Form::open([ 'url' => 'ventas_pos_form_registro_ingresos_gastos','id'=>'form_registrar_ingresos_gastos','files' => true]) }}
	<div class="container-fluid">
		<div class="form-group">
			<label class="control-label col-sm-3" for="motivo_input">*Motivo:</label>
			<div class="col-sm-9">
				<input class="form-control text_input_sugerencias" id="motivo_input" data-url_busqueda="{{url('/')}}/teso_consultar_motivos" autocomplete="off" name="motivo_input" type="text">
				<input type="hidden" name="campo_motivos" id="combobox_motivos" required="required" value="">
			</div>
		</div>

		<br><br>

		<div class="form-group">
			<label class="control-label col-sm-3" for="cliente_proveedor_id">*Cliente/Proveedor:</label>
			<div class="col-sm-9">
				<input placeholder="*Cliente/Proveedor" autocomplete="off" class="form-control text_input_sugerencias" data-url_busqueda="{{url('/')}}/core_consultar_terceros_v2" data-clase_modelo="App\Core\Tercero" required="required" name="core_tercero_id_aux" type="text" value="">
				<input type="hidden" name="cliente_proveedor_id" id="cliente_proveedor_id" required="required" value="">
			</div>
		</div>

		<br><br>


		<div class="form-group">
			<label class="control-label col-sm-3" for="detalle_operacion">Detalle:</label>
			<div class="col-sm-9">
				<input class="form-control" id="detalle_operacion" name="detalle_operacion" type="text">
			</div>
		</div>

		<br><br>

		<div class="form-group">
			<label class="control-label col-sm-3" for="col_valor">*Valor:</label>
			<div class="col-sm-9">
				<input id="col_valor" class="form-control" name="col_valor" type="text" required="required">
			</div>
		</div>

	</div>

	<input type="hidden" name="core_tipo_transaccion_id" value="{{ $campos->core_tipo_transaccion_id }}">
	<input type="hidden" name="core_tipo_doc_app_id" value="{{ $campos->core_tipo_doc_app_id }}">
	<input type="hidden" name="consecutivo" value="{{ $campos->consecutivo }}">
	<input type="hidden" name="fecha" value="{{ $campos->fecha }}">
	<input type="hidden" name="core_empresa_id" value="{{ $campos->core_empresa_id }}">
	<input type="hidden" name="teso_medio_recaudo_id" value="{{ $campos->teso_medio_recaudo_id }}">
	<input type="hidden" name="teso_caja_id" value="{{ $campos->teso_caja_id }}">
	<input type="hidden" name="teso_cuenta_bancaria_id" value="{{ $campos->teso_cuenta_bancaria_id }}">
	<input type="hidden" name="estado" value="{{ $campos->estado }}">
	<input type="hidden" name="creado_por" value="{{ $campos->creado_por }}">
	<input type="hidden" name="id_modelo" value="{{ $campos->id_modelo }}">

{{ Form::close() }}