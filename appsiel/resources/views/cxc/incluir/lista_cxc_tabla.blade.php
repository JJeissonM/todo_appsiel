<table class="table table-bordered" id="documentos_pendientes">
    {{ Form::bsTableHeader(['Inmueble','Documento','Fecha','Fecha Vence','Vlr. cartera','Saldo pend.','Vlr. aplicar']) }}
    <tbody>
        <?php
        for($i=0;$i<count($movimiento_cxc);$i++){ 
            $id = $movimiento_cxc[$i]['campo7'];
            ?>
            <tr id="{{ $id }}">
                <td> {{ $movimiento_cxc[$i]['codigo'] }} </td>
                <td> {{ $movimiento_cxc[$i]['campo1'] }} </td>
                <td> {{ $movimiento_cxc[$i]['campo2'] }} </td>
                <td> {{ $movimiento_cxc[$i]['campo3'] }} </td>
                <td class="col_valor_cartera"> {{ number_format($movimiento_cxc[$i]['campo4'], 0, ',', '.') }} </td>
                <td class="col_saldo_pendiente" > {{ number_format($movimiento_cxc[$i]['campo6'], 0, ',', '.') }} </td>
                <td> 
                    {{ Form::text('text_aplicar_'.$id, $movimiento_cxc[$i]['campo6'], [ 'id' => 'text_aplicar_'.$id, 'class' => 'text_aplicar' ] ) }} 
                    <button class="btn btn-success btn-xs btn_agregar_documento"><i class="fa fa-check"></i></button>
                </td>
            </tr>
        <?php 
        } ?>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td colspan="3">Pendiente x aplicar (Anticipo)</td>
            <td>{{ Form::text( 'pendiente_aplicar', null, [ 'id' => 'pendiente_aplicar', 'disabled' => 'disabled' ] ) }} <button class="btn btn-success btn-xs btn_agregar_documento"><i class="fa fa-check"></i></button></td>
        </tr>                       
    </tfoot>
</table>