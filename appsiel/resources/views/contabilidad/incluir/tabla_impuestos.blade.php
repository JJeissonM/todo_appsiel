<?php
	$total_debitos = 0;
    $total_creditos = 0;
    $j=0;
?>

<h3 style="width: 100%; text-align: center;"> Impuestos </h3>
<hr>
<div class="table-responsive">
    <table id="myTable" class="table table-striped" style="margin-top: -4px;">
        {{ Form::bsTableHeader(['Transacción','Impuesto','Tasa', 'Cód. Cta.', 'Cuenta', 'Producto', 'Tasa Mov.', 'DB', 'CR']) }}
        <tbody>
            @foreach($movimiento as $fila)
                <tr class="fila-{{$j}}">
                    <td> {{ $fila->transaccion_descripcion }} </td>
                    <td> {{ $fila->impuesto_descripcion }} </td>
                    <td> {{ $fila->impuesto_tasa }} </td>
                    <td class="text-center"> {{ $fila->cuenta_codigo }} </td>
                    <td> {{ $fila->cuenta_descripcion }} </td>
                    <td> {{ $fila->producto_descripcion }} ({{ $fila->producto_unidad_medida }}) </td>
                    <td> {{ $fila->movimiento_tasa }} </td>
                    <td class="text-center"> {{ number_format( $fila->valor_debito, 0, ',', '.') }} </td>
                    <td class="text-center"> {{ number_format( $fila->valor_credito, 0, ',', '.') }} </td>
                </tr>
                <?php
                    $j++;
                    if ($j == 3)
                    {
                        $j=1;
                    }
                    $total_debitos += $fila->valor_debito;
                    $total_creditos += $fila->valor_credito;
                ?>
            @endforeach

            <tr class="fila-{{$j}}" >
                <td colspan="7">
                   &nbsp;
                </td>
                <td class="text-center">
                   {{ number_format($total_debitos, 0, ',', '.')}}
                </td>
                <td class="text-center">
                   {{ number_format($total_creditos, 0, ',', '.')}}
                </td>
            </tr>
        </tbody>
    </table>
</div>