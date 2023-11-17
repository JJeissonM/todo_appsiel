<?php 
    //dd($cajas, $cuentas_bancarias);
?>
<div class="container-fluid">
    <div class="marco_formulario">
        <h5>Propina sugerida</h5>
        <hr>
        <div id="div_ingreso_registro_propina">
            <br>
            <div class="table-responsive" id="table_propina">
                <table class="table table-striped" id="ingreso_registro_propina">
                    {{ Form::bsTableHeader( [ 'Medio de pago', 'Caja/Banco', 'Valor'] ) }}
                    <tbody>
                        <tr>
                            <td>
                                <select id="teso_medio_recaudo_id_propina" class="form-control" name="teso_medio_recaudo_id_propina">
                                    @foreach ($medios_recaudo as $id => $label)
                                        <option value="{{$id}}">{{$label}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div id="div_caja_propina">
                                    <select id="teso_caja_id_propina" class="form-control" name="teso_caja_id_propina">
                                        <option value=""></option>
                                        @foreach ($cajas as $id => $label)
                                            <option value="{{$id}}">{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="div_banco_propina" style="display:none;">
                                    <select id="teso_cuenta_bancaria_id_propina" class="form-control" name="teso_cuenta_bancaria_id_propina">
                                        <option value=""></option>
                                        @foreach ($cuentas_bancarias as $id => $label)
                                            <option value="{{$id}}">{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <input class="form-control" id="valor_propina" placeholder="Valor" autocomplete="off" name="valor_propina" type="text" value="{{ $valor_lbl_propina }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>