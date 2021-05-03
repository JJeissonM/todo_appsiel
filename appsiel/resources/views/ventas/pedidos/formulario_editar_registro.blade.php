{{ Form::open(['url'=>'vtas_doc_registro_guardar?id='.$id.'&id_modelo='.$id_modelo.'&id_transaccion='.$id_transaccion,'id'=>'form_edit']) }}
  <h4>{{ $linea_factura->producto_descripcion }}</h4>
  <hr>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('precio_unitario', $linea_factura->precio_unitario, 'Precio unitario', ['id'=>'precio_unitario']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('tasa_impuesto', $linea_factura->tasa_impuesto, 'Tasa impuesto', ['disabled'=>'disabled','id'=>'tasa_impuesto','width'=>'15px']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('cantidad', $linea_factura->cantidad, 'Cantidad', ['id'=>'cantidad']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('tasa_descuento', $linea_factura->tasa_descuento, 'Tasa descuento', [ 'id'=>'tasa_descuento','width'=>'15px']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('valor_total_descuento_no', $linea_factura->valor_total_descuento, 'Valor total descuento', ['disabled'=>'disabled','id'=>'valor_total_descuento_no']) }}
  </div>

  <div class="row" style="padding:5px;">
    {{ Form::bsText('precio_total', $linea_factura->precio_total, 'Precio total', ['disabled'=>'disabled','id'=>'precio_total']) }}
  </div>
  
  <input type="hidden" name="cantidad_original" id="cantidad_original" value="{{ $linea_factura->cantidad }}">

  <input type="hidden" name="valor_total_descuento" id="valor_total_descuento" value="{{ $linea_factura->valor_total_descuento }}">

  {{ Form::hidden('linea_factura_id', $linea_factura->id ) }}

{{ Form::close()}}