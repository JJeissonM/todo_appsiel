<?php
	$total_debitos = 0;
    $total_creditos = 0;
    $j=0;
?>

<h3 style="width: 100%; text-align: center;"> Impuestos </h3>
<hr>

<table id="myTable" class="table table-striped" style="margin-top: -4px;">

    <tr class="fila-{{$j}}">
        <td colspan="9"> Ingresos y devoluciones por ventas </td>
    </tr>

    <?php
        $j++;
        if ($j == 3)
        {
            $j=1;
        }
    ?>

    <tr class="fila-{{$j}}">
        <td> Transacción </td>
        <td> Impuesto </td>
        <td> Tasa </td>
        <td> Cód. Cta. </td>
        <td> Cuenta </td>
        <td> Producto </td>
        <td> Tasa Mov. </td>
        <td> DB </td>
        <td> CR </td>
    </tr>



    <?php
        $j++;
        if ($j == 3)
        {
            $j=1;
        }
    ?>

    @foreach($ingresos as $fila)
        <tr class="fila-{{$j}}">
            <td> {{ $fila->transaccion_descripcion }} </td>
            <td> {{ $fila->impuesto_descripcion }} </td>
            <td> {{ $fila->impuesto_tasa }} </td>
            <td> {{ $fila->cuenta_codigo }} </td>
            <td> {{ $fila->cuenta_descripcion }} </td>
            <td> {{ $fila->producto_descripcion }} ({{ $fila->producto_unidad_medida }}) </td>
            <td> {{ $fila->movimiento_tasa }} </td>
            <td> {{ number_format( $fila->valor_debito, 0, ',', '.') }} </td>
            <td> {{ number_format( $fila->valor_credito, 0, ',', '.') }} </td>
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
        <td>
           {{ number_format($total_debitos, 0, ',', '.')}}
        </td>
        <td>
           {{ number_format($total_creditos, 0, ',', '.')}}
        </td>
    </tr>

    <?php
        $total_debitos = 0;
        $total_creditos = 0;
        $j=0;
    ?>

    <tr class="fila-{{$j}}">
        <td colspan="9"> Compras y devoluciones por compras </td>
    </tr>
    <?php
        $j++;
        if ($j == 3)
        {
            $j=1;
        }
    ?>
    <tr class="fila-{{$j}}">
        <td> Transacción </td>
        <td> Impuesto </td>
        <td> Tasa </td>
        <td> Cód. Cta. </td>
        <td> Cuenta </td>
        <td> Producto </td>
        <td> Tasa Mov. </td>
        <td> DB </td>
        <td> CR </td>
    </tr>
    <?php
        $j++;
        if ($j == 3)
        {
            $j=1;
        }
    ?>
    @foreach($compras as $fila)
        <tr class="fila-{{$j}}">
            <td> {{ $fila->transaccion_descripcion }} </td>
            <td> {{ $fila->impuesto_descripcion }} </td>
            <td> {{ $fila->impuesto_tasa }} </td>
            <td> {{ $fila->cuenta_codigo }} </td>
            <td> {{ $fila->cuenta_descripcion }} </td>
            <td> {{ $fila->producto_descripcion }} ({{ $fila->producto_unidad_medida }}) </td>
            <td> {{ $fila->movimiento_tasa }} </td>
            <td> {{ number_format( $fila->valor_debito, 0, ',', '.') }} </td>
            <td> {{ number_format( $fila->valor_credito, 0, ',', '.') }} </td>
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
        <td>
           {{ number_format($total_debitos, 0, ',', '.')}}
        </td>
        <td>
           {{ number_format($total_creditos, 0, ',', '.')}}
        </td>
    </tr>
</table>