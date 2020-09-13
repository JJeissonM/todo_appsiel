<?php
	$estilo_text=' style="border: none;border-color: transparent;border-bottom: 1px solid gray;"';
?>

<div id="recaudoModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Agregar registros <small style="color: red;"> «Ingrese el valor y presione Enter para agregar.»</small> </h4>
      </div>
      <div class="modal-body">
          <div id="form_registro">
            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('teso_motivo_id',null,'Motivo',$motivos,[]) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('teso_medio_recaudo_id', null, 'Medio recaudo', $medios_recaudo, []) }}
            </div>

            <div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>

            <div class="row" style="padding:5px; display: none;" id="div_caja">
                {{ Form::bsSelect('teso_caja_id', null, 'Caja',$cajas, []) }}
            </div>

            <div class="row" style="padding:5px; display: none;" id="div_cuenta_bancaria">
                {{ Form::bsSelect('teso_cuenta_bancaria_id', null, 'Cuenta Bancaria',$cuentas_bancarias, []) }}
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('valor_total', null, 'Valor', []) }}
            </div>

            <input id="id_transaccion" type="hidden" name="id_transaccion" value="{{$id_transaccion}}">
        </div>
      </div>
      <div class="modal-footer">
        <button id="btn_agregar" type="button" class="btn btn-success" style="display: none;">Agregar</button>
      </div>
    </div>

  </div>
</div>    