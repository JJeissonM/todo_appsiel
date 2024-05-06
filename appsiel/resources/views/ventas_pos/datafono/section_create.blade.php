<div class="container-fluid">
    <div class="marco_formulario">
        <h5>Com. Datafono</h5>
        <hr>
        <div id="div_ingreso_registro_datafono">
            <br>
            <div class="table-responsive" id="table_datafono">
                <table class="table table-striped" id="ingreso_registro_datafono">
                    {{ Form::bsTableHeader( [ 'Medio de pago', 'Caja/Banco', 'Valor'] ) }}
                    <tbody>
                        <tr>
                            <td>
                                <select id="teso_medio_recaudo_id_datafono" class="form-control" name="teso_medio_recaudo_id_datafono">
                                    @foreach ($medios_recaudo as $id => $label)
                                        <option value="{{$id}}">{{$label}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div id="div_caja_datafono">
                                    <select id="teso_caja_id_datafono" class="form-control" name="teso_caja_id_datafono">
                                        <option value=""></option>
                                        @foreach ($cajas as $id => $label)
                                            <option value="{{$id}}">{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="div_banco_datafono" style="display:none;">
                                    <select id="teso_cuenta_bancaria_id_datafono" class="form-control" name="teso_cuenta_bancaria_id_datafono">
                                        <option value=""></option>
                                        @foreach ($cuentas_bancarias as $id => $label)
                                            <option value="{{$id}}">{{$label}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td>
                                <input class="form-control" id="valor_datafono" placeholder="Valor" autocomplete="off" name="valor_datafono" type="text" value="{{ $valor_lbl_datafono }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>