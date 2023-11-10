<div class="container-fluid">
    <div class="marco_formulario">
        <h5>Propina sugerida</h5>
        <hr>
        <div id="div_ingreso_registro_propina">
            <br>
            <div class="table-responsive" id="table_propina">
                <table class="table table-striped" id="ingreso_registro_propina">
                    <tbody>
                        <tr>
                            <td>{{ Form::bsSelect('teso_medio_recaudo_id_propina', null, 'Medio recaudo', $medios_recaudo, []) }}</td>
                            <td>
                                <div id="div_caja_propina">
                                    {{ Form::bsSelect('teso_caja_id_propina', null, 'Caja', array_merge(['0'=>''], $cajas ), []) }}
                                </div>
                                <div id="div_banco_propina" style="display:none;">
                                    {{ Form::bsSelect('teso_cuenta_bancaria_id_propina', null, 'Cuenta Bancaria', array_merge(['0'=>''], $cuentas_bancarias ), []) }}
                                </div>
                            </td>
                            <td>{{ Form::bsText('valor_total_propina', 0, 'Valor', []) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>