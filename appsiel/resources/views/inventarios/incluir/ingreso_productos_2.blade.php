<?php
	$estilo_text=' style="border: none;border-color: transparent;border-bottom: 1px solid gray;"';
?>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar productos <small style="color: red;"> &#171;En cada campo presione Enter para continuar.&#187;</small></h4>
      </div>
      <div class="modal-body">
        {{ Form::open(['url'=>'post_ajax','id'=>'form_producto']) }}

            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('motivo',null,'Motivo',$motivos,[]) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('inv_producto_id', null, 'Producto', $productos, []) }}
            </div>

            <div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('existencia_actual', null, 'Existencia actual', ['disabled'=>'disabled']) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('costo_unitario', null, 'Costo unitario', ['disabled'=>'disabled']) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('cantidad', null, 'Cantidad', ['disabled'=>'disabled']) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('costo_total', null, 'Costo total', ['disabled'=>'disabled']) }}
            </div>

            <input id="id_transaccion" type="hidden" name="id_transaccion" value="{{$id_transaccion}}">
            <input id="id_bodega" type="hidden" name="id_bodega"> 
            <input id="unidad_medida1" type="hidden" name="unidad_medida1">
            <input id="tipo_producto" type="hidden" name="tipo_producto">
            <input id="fecha_aux" type="hidden" name="fecha_aux">

            <input type="hidden" name="saldo_original" id="saldo_original" value="0">
            <input type="hidden" name="cantidad_original" id="cantidad_original" value="0">
            <input type="hidden" name="cantidad_anterior" id="cantidad_anterior" value="0">
          
        {{ Form::close() }}
      </div>
      <div class="modal-footer">
        <button id="btn_agregar" type="button" class="btn btn-success">Agregar</button>
      </div>
    </div>

  </div>
</div>    