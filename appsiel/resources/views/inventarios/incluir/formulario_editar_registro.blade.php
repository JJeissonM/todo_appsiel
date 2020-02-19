{{ Form::open(['url'=>'inv_doc_registro_guardar?id='.$id.'&id_modelo='.$id_modelo.'&id_transaccion='.$id_transaccion,'id'=>'form_edit']) }}
  <h4>{{ $linea_registro->producto_descripcion }}</h4>
  <hr>

  <div class="row" style="padding:5px;">
    <b>Documento Inventario: </b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
    <br>
    <b>Fecha: </b> {{ $doc_encabezado->fecha }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('costo_unitario', $linea_registro->costo_unitario, 'Costo unitario', ['id'=>'costo_unitario']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('saldo_a_la_fecha', $saldo_a_la_fecha, 'Saldo a la fecha', ['disabled'=>'disabled','id'=>'saldo_a_la_fecha']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('cantidad', abs($linea_registro->cantidad), 'Cantidad', ['id'=>'cantidad']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('costo_total', abs($linea_registro->costo_total), 'Costo total', ['disabled'=>'disabled','id'=>'costo_total']) }}
  </div>
  
  <input type="hidden" name="saldo_original" id="saldo_original" value="{{ $saldo_a_la_fecha }}">
  <input type="hidden" name="cantidad_original" id="cantidad_original" value="{{ abs($linea_registro->cantidad) }}">

  <input type="hidden" name="cantidad_anterior" id="cantidad_anterior" value="{{ abs($linea_registro->cantidad) }}">
  <input type="hidden" name="saldo_a_la_fecha2" id="saldo_a_la_fecha2" value="{{ $saldo_a_la_fecha }}">
  <input type="hidden" name="producto_id" id="producto_id" value="{{ $linea_registro->producto_id }}">
  <input type="hidden" name="bodega_id" id="bodega_id" value="{{ $linea_registro->inv_bodega_id }}">
  <input type="hidden" name="motivo_movimiento"  id="motivo_movimiento" value="{{ $linea_registro->motivo_movimiento }}">
  <input type="hidden" name="fecha" id="fecha" value="{{ $doc_encabezado->fecha }}">

  {{ Form::hidden('linea_registro_id', $linea_registro->id ) }}

{{ Form::close()}}