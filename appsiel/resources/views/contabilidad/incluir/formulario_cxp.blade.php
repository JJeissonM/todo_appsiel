{{ Form::open( [ 'url'=>'' ] ) }}
  <h4> Ingreso registro de CxP</h4>
  <hr>

  <div class="row" style="padding:5px;">
    {{ Form::bsFecha('fecha_vencimiento_aux', null, 'Fecha vencimiento', [] , [] ) }}
  </div>

  <div class="row" style="padding:5px;">

    <div class="form-group">
      <label class="control-label col-sm-3" for="cuenta_input_aux">Cuenta:</label>
      <div class="col-sm-9">
        {{ Form::text( 'cuenta_input_aux', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'cuenta_input_aux', 'data-url_busqueda' => url('contab_consultar_cuentas'), 'autocomplete'  => 'off' ] ) }}
        {{ Form::hidden( 'campo_cuentas_aux', null, [ 'id' => 'combobox_cuentas_aux' ] ) }}
      </div>
    </div>
    
  </div>

  <div class="row" style="padding:5px;">

    <div class="form-group">
      <label class="control-label col-sm-3" for="core_tercero_id_aux">Tercero:</label>
      <div class="col-sm-9">
        {{ Form::text( 'core_tercero_id_aux', null, [ 'class' => 'form-control text_input_sugerencias', 'id' => 'tercero_input_aux', 'data-url_busqueda' => url('core_consultar_terceros_v2'), 'autocomplete'  => 'off' ] ) }}
        {{ Form::hidden( 'campo_terceros_aux', null, [ 'id' => 'combobox_terceros_aux' ] ) }}
      </div>
    </div>
    
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('documento_soporte_tercero_aux', null, 'Documento soporte tercero', []) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('detalle_aux', null, 'Detalle', []) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('valor_credito_aux', null, 'Valor Crédito', []) }}
  </div>

  <input type="hidden" name="tipo_transaccion_linea_aux" id="tipo_transaccion_linea_aux" value="crear_cxp">
  <input type="hidden" name="valor_debito_aux" id="valor_debito_aux" value="">

{{ Form::close()}}