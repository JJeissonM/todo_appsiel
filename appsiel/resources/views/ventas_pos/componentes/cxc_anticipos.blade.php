<?php 
    //use App\CxC\CxcEstadoCartera;
?>
<table class="table table-bordered" id="documentos_afavor" width="100%">    
    <thead>
        <tr>
            <th data-override="checkbox"><input type="checkbox" class="btn-gmail-check" id="checkbox_head"></th>
            <td>Documento</td>
            <td>Fecha</td>
            <td>Detalle</td>
            <td>Saldo a favor</td>
        </tr>
    </thead>    
    <tbody>
        <?php
        $total_saldo = 0;
        for($i=0;$i<count($movimiento_cxc);$i++)
        { 
            $id = $movimiento_cxc[$i]['id'];

            $class_advertencia = '';
            $advertencia = '';
            $ocultar = '';

        ?>
        @if( $movimiento_cxc[$i]['saldo_pendiente'] < 0 )
            <tr id="{{ $id }}" class="{{$class_advertencia}}">
                <td>
                    <input type="checkbox" value="0" class="btn-gmail-check checkbox_fila" name="checkbox_fila[]" data-valor_aplicar={{ $movimiento_cxc[$i]['saldo_pendiente'] * -1 }} data-cxc_movimiento_id={{ $id }}>
                    <span class="checkbox_aux" style="color: transparent;">0</span>
                </td>
                <td class="text-center"> {{ $movimiento_cxc[$i]['documento'] }} </td>
                <td> {{ $movimiento_cxc[$i]['fecha'] }} </td>
                <td> {{ $movimiento_cxc[$i]['detalle'] }} </td>
                <td class="col_saldo_pendiente text-right" >
                    <?php
                        $saldo_pendiente = $movimiento_cxc[$i]['saldo_pendiente'] * -1;

                        $total_saldo += $saldo_pendiente;
                    ?> 
                    {{ number_format($saldo_pendiente, 0, ',', '.') }} 
                </td>               
            </tr>
        @endif
        <?php 
        } ?>

        <tr>
            <td colspan="4"></td>
            <td class="col_saldo_pendiente text-right" >
                $ {{ number_format($total_saldo, 0, ',', '.') }} 
            </td>
        </tr>

    </tbody>
</table>

<div class="row">
    <div class="col-md-12">
        <b>Valor a aplicar:</b><span id="label_anticipo_aplicar" class="text-right"> $ 0 </span>
		<input type="hidden" name="valor_anticipo_aplicar" id="valor_anticipo_aplicar" value="0">
        <button class="btn btn-success btn-sm" id="btn_aplicar_anticipo"> <i class="fa fa-check"></i> Aplicar anticipo </button>
    </div>
</div>