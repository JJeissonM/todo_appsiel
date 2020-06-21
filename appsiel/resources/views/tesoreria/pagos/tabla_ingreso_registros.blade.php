<h4>Ingreso de valores</h4>
<hr>
<div id="div_ingreso_registros">
    <div class="table-responsive" id="table_content">
        <table class="table table-striped" id="ingreso_registros">
            <thead>
                <tr>
                    <th data-override="teso_motivo_id" width="200px">Motivo</th>
                    <th data-override="linea_tercero_id" width="200px">Tercero</th>
                    <th data-override="detalle">Detalle</th>
                    <th data-override="valor">Valor</th>
                    <th width="10px">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                {!! $lineas_tabla_ingreso_registros !!}
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <button id="btn_nuevo" style="background-color: transparent; color: #3394FF; border: none;"><i class="fa fa-btn fa-plus"></i> Agregar registro</button>
                    </td>
                    <td width="200px"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td> <div id="total_valor">$0</div> </td>
                    <td> &nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">&nbsp;</td>
                    <td> <div id="lbl_total_pendiente" style="color: red;"></div></td>
                    <td> <div id="total_pendiente" style="color: red;"></div> </td>
                    <td> &nbsp;</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>