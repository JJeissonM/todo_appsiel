<?php
	$estilo_text=' style="border: none;border-color: transparent;border-bottom: 1px solid gray;"';
    $usar_modal_botones_medios_pago = (bool)($usar_modal_botones_medios_pago ?? false);
    $modal_botones_medios_pago_data = $modal_botones_medios_pago_data ?? ['medios' => [], 'destinos' => []];
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

            @if(!$usar_modal_botones_medios_pago)
            <div class="row" style="padding:5px;">
                {{ Form::bsSelect('teso_medio_recaudo_id', null, 'Medio recaudo', $medios_recaudo, []) }}
            </div>
            @else
            <style>
                .pos-payment-options {
                    display: flex;
                    flex-wrap: wrap;
                    gap: 10px;
                    padding-top: 4px;
                }
                .pos-payment-options .btn {
                    min-width: 120px;
                    border-width: 2px;
                    border-radius: 2px;
                    white-space: normal;
                    position: relative;
                    padding-top: 20px;
                }
                .pos-payment-options .btn.btn-selected {
                    background: #f5f5f5;
                    font-weight: 600;
                }
                .pos-payment-options .btn .shortcut-badge {
                    position: absolute;
                    top: 4px;
                    left: 6px;
                    display: inline-block;
                    min-width: 20px;
                    height: 20px;
                    line-height: 20px;
                    border-radius: 10px;
                    background: #2c3e50;
                    color: #fff;
                    font-size: 11px;
                    font-weight: bold;
                    text-align: center;
                }
                .pos-payment-options.pos-payment-options-caja .btn {
                    border-color: #22a9d6;
                }
                .pos-payment-options.pos-payment-options-caja .btn.btn-selected {
                    background: #e8f9ff;
                }
                .pos-payment-options.pos-payment-options-cuenta .btn {
                    border-color: #f39c12;
                }
                .pos-payment-options.pos-payment-options-cuenta .btn.btn-selected {
                    background: #fff4df;
                }
                .pos-payment-options.pos-payment-options-medio .btn {
                    border-color: #e74c3c;
                }
                .pos-payment-options.pos-payment-options-medio .btn.btn-selected {
                    background: #fff0ee;
                }
            </style>

            <input type="hidden" id="teso_medio_recaudo_id" value="">
            <input type="hidden" id="teso_caja_id" value="">
            <input type="hidden" id="teso_cuenta_bancaria_id" value="">
            <input type="hidden" id="usar_modal_botones_medios_pago" value="1">

            <script type="application/json" id="modal_botones_medios_pago_data_json">{!! json_encode($modal_botones_medios_pago_data) !!}</script>

            <div class="row" style="padding:5px;">
                <div class="form-group">
                    <label class="control-label col-sm-3">Medio recaudo</label>
                    <div class="col-sm-9">
                        <div class="pos-payment-options pos-payment-options-medio" id="grupo_botones_teso_medio_recaudo">
                            <?php $shortcut = 1; ?>
                            @foreach($modal_botones_medios_pago_data['medios'] as $medio)
                                <button type="button"
                                        class="btn btn-default btn_pos_payment_option"
                                        data-option-type="medio"
                                        data-value="{{ $medio['value'] }}"
                                        data-label="{{ $medio['label'] }}"
                                        data-id="{{ $medio['id'] }}"
                                        data-shortcut="{{ $shortcut }}">
                                    <span class="shortcut-badge">{{ $shortcut }}</span>
                                    {{ $medio['label'] }}
                                </button>
                                <?php $shortcut++; ?>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="row" id="spin" style="display: none;">
                <img src="{{asset('assets/img/spinning-wheel.gif')}}" width="32px" height="32px">
            </div>

            <div class="row" style="padding:5px; display: none;" id="div_caja">
                @if(!$usar_modal_botones_medios_pago)
                    {{ Form::bsSelect('teso_caja_id', null, 'Caja',$cajas, []) }}
                @else
                    <div class="form-group">
                        <label class="control-label col-sm-3">Caja</label>
                        <div class="col-sm-9">
                            <div class="pos-payment-options pos-payment-options-caja" id="grupo_botones_teso_caja"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row" style="padding:5px; display: none;" id="div_cuenta_bancaria">
                @if(!$usar_modal_botones_medios_pago)
                    {{ Form::bsSelect('teso_cuenta_bancaria_id', null, 'Cuenta Bancaria',$cuentas_bancarias, []) }}
                @else
                    <div class="form-group">
                        <label class="control-label col-sm-3">Cuenta Bancaria</label>
                        <div class="col-sm-9">
                            <div class="pos-payment-options pos-payment-options-cuenta" id="grupo_botones_teso_cuenta_bancaria"></div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="row" style="padding:5px;">
                {{ Form::bsText('valor_total', null, 'Valor', []) }}
            </div>

            <input id="id_transaccion" type="hidden" name="id_transaccion" value="{{$id_transaccion}}">
            @if(!$usar_modal_botones_medios_pago)
            <input type="hidden" id="usar_modal_botones_medios_pago" value="0">
            @endif
        </div>
      </div>
      <div class="modal-footer">
        <button id="btn_agregar" type="button" class="btn btn-success" style="display: none;">Agregar</button>
      </div>
    </div>

  </div>
</div>    
