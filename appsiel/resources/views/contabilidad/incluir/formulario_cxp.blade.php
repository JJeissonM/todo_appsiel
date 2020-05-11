{{ Form::open( [ 'url'=>'' ] ) }}
  <h4> Ingreso registro de CxP</h4>
  <hr>

  <div class="row" style="padding:5px;">
    {{ Form::bsFecha('fecha_vencimiento_aux', null, 'Fecha vencimiento', [] , [] ) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsSelect('contab_cuenta_id_aux', null, 'Cuenta', $cuentas , [] ) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsSelect('core_tercero_id_aux', null, 'Tercero', $terceros , [] ) }}
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