<table class="table table-striped table-bordered" id="tabla_cheques_recibidos">
    <thead>
        <tr>
            <th style="display: none;">cheque_id</th>
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

            <?php
                $descripcion_entidad_financiera = ''; 
                if ( !is_null( $cheque->entidad_financiera ) )
                {
                    $descripcion_entidad_financiera = $cheque->entidad_financiera->descripcion;
                }
            ?>
            <tr>
                <td style="display: none;">{{ $cheque->id }}</td>
                <td style="display: none;" class="tipo_operacion_id_cheque">{{ $tipo_operacion_id }}</td>
                <td style="display: none;">{{ $teso_motivo_id }}</td>
                <td style="display: none;">&nbsp;</td>
                <td style="display: none;">{{ $caja_id }}</td>
                <td style="display: none;">{{ $cheque->entidad_financiera_id }}</td>
                <td style="display: none;"> <div class="valor_cheque">{{ $cheque->valor }}</td>
                <td> {{ $cheque->fecha_emision }} </td>
                <td> {{ $cheque->fecha_cobro }} </td>
                <td> {{ $cheque->numero_cheque }} </td>
                <td> {{ $cheque->referencia_cheque }} </td>
                <td> {{ $descripcion_entidad_financiera }} </td>
                <td align="right"> $ {{ $cheque->valor }} </td>
                <td> 
                    <button type='button' class='btn btn-success btn-xs btn_seleccionar_cheque'><i class='fa fa-btn fa-check'></i></button>
                    <button type='button' class='btn btn-danger btn-xs btn_eliminar_cheque' style="display: none;"><i class='fa fa-btn fa-trash'></i></button>
                </td>
            </tr>
        @endforeach            
    </tbody>
</table>