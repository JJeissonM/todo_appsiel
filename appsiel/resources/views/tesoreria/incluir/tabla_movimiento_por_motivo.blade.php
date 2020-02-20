<?php
    $total_valor_movimiento = 0;
?>
<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th> Motivo </th>
            <th> Valor </th>
        </tr>
    </thead>
    <tbody>
        @foreach($movimiento as $linea)
            <?php 
                $valor_movimiento = abs($linea['valor_movimiento']);

                $total_valor_movimiento += $valor_movimiento;
            ?>

            <tr>
                <td>
                    {{ $linea['motivo'] }}
                </td>
                <td>
                   {{ number_format($valor_movimiento, 0, ',', '.') }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td>{{ number_format($total_valor_movimiento, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>