<table class="table table-striped table-bordered" id="tabla_registros_cheques">
    <thead>
        <tr>
            <th style="display: none;">tipo_operacion_id_cheque</th>
            <th style="display: none;">teso_motivo_id_cheque</th>
            <th style="display: none;">detalle_cheque</th>
            <th style="display: none;">caja_id_cheque</th>
            <th style="display: none;">entidad_financiera_id</th>
            <th style="display: none;">valor_cheque</th>
            <th data-override="fecha_emision">F. Emisión</th>
            <th data-override="fecha_cobro">F. cobro</th>
            <th data-override="numero_cheque">Núm.</th>
            <th data-override="referencia_cheque">Ref.</th>
            <th>Banco</th>
            <th>Valor</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @foreach( $cheques AS $cheque )
            <td style="display: none;" class="tipo_operacion_id_cheque">
                {{ $cheque->tipo_operacion_id_cheque}} 
            </td>
            <td style="display: none;"> {{ $cheque->teso_motivo_id_cheque }} </td>
            <td style="display: none;"> {{ $cheque->detalle_cheque }} </td>
            <td style="display: none;"> {{ $cheque->caja_id_cheque }} </td>
            <td style="display: none;"> {{ $cheque->entidad_financiera_id }} </td>
            <td style="display: none;"> <div class="valor_cheque"> {{ $cheque->valor_cheque }} </td>
            <td> {{ $cheque->fecha_emision }} </td>
            <td> {{ $cheque->fecha_cobro }} </td>
            <td> {{ $cheque->numero_cheque }} </td>
            <td> {{ $cheque->referencia_cheque }} </td>
            <td> {{ $cheque->entidad_financiera_id }} </td>
            <td align="right"> $ {{ $cheque->valor_cheque }} </td>
            <td> 
                <button type='button' class='btn btn-success btn-xs btn_seleccionar_cheque'><i class='fa fa-btn fa-check'></i></button> 
            </td>
        @endforeach            
    </tbody>
</table>