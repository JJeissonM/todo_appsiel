{{ Form::open(['url'=>'compras_doc_registro_guardar?id='.$id.'&id_modelo='.$id_modelo.'&id_transaccion='.$id_transaccion,'id'=>'form_edit']) }}
  <h4>{{ $linea_factura->producto_descripcion }}</h4>
  <hr>

  <div class="row" style="padding:5px;">
    <b>Fecha entrada de almacén: </b> {{ $entrada_almacen->fecha }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('precio_unitario', $linea_factura->precio_unitario, 'Precio unitario', ['id'=>'precio_unitario']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('tasa_impuesto', $linea_factura->tasa_impuesto, 'Tasa impuesto', ['disabled'=>'disabled','id'=>'tasa_impuesto','width'=>'15px']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('saldo_a_la_fecha', $saldo_a_la_fecha, 'Saldo a la fecha', ['disabled'=>'disabled','id'=>'saldo_a_la_fecha']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('cantidad', $linea_factura->cantidad, 'Cantidad', ['id'=>'cantidad']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('precio_total', $linea_factura->precio_total, 'Precio total', ['disabled'=>'disabled','id'=>'precio_total']) }}
  </div>
  
  <input type="hidden" name="cantidad_anterior" id="cantidad_anterior" value="{{ $linea_factura->cantidad }}">
  <input type="hidden" name="saldo_a_la_fecha2" id="saldo_a_la_fecha2" value="{{ $saldo_a_la_fecha }}">
  <input type="hidden" name="producto_id" id="producto_id" value="{{ $linea_entrada_almacen->inv_producto_id}}">
  <input type="hidden" name="bodega_id" id="bodega_id" value="{{ $linea_entrada_almacen->inv_bodega_id }}">
  <input type="hidden" name="fecha" id="fecha" value="{{ $entrada_almacen->fecha }}">

  {{ Form::hidden('linea_factura_id', $linea_factura->id ) }}

{{ Form::close()}}