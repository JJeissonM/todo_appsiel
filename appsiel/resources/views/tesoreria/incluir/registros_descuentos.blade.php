<?php
    // Se llaman dedsde el movimiento contable
    $descuentos = $doc_encabezado->get_resgitros_descuentos();
?>
@if( !empty( $descuentos->toArray() ) )
<table class="table table-bordered">
    <tr>
        <td style="text-align: center; background-color: #ddd;"> 
            <span style="text-align: right; font-weight: bold;"> REGISTROS DE DESCUENTOS </span> 
        </td>
    </tr>
</table>
<div class="table-responsive contenido">
    <table class="table table-bordered table-striped">
        {{ Form::bsTableHeader(['Fecha','Detalle','Valor']) }}
        <tbody>
            <?php
                $total_descuentos = 0;
            ?>
            @foreach( $descuentos as $descuento )
                <tr>
                    <td> {{ $descuento->fecha }} </td>
                    <td> {{ $descuento->detalle_operacion }} </td>
                    <td class="text-right"> $ {{ number_format( abs($descuento->valor_saldo), 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_descuentos += abs($descuento->valor_saldo);
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td colspan="2" style="text-align: right;"> Total Dctos. </td>
                <td class="text-right"> $ {{ number_format($total_descuentos, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
</div>
@endif