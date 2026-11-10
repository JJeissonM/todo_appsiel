<?php
	$total_debitos = 0;
    $total_creditos = 0;
    $total_saldo_inicial = 0;
    $total_saldo_final = 0;
    $j=0;
?>

<h3 style="width: 100%; text-align: center;"> Balance de comprobación </h3>
<hr>

<div class="table-responsive">
    <table id="myTable" class="table table-striped" style="margin-top: -4px;">
        <thead>
            <tr>
                <th>
                   Cuenta
                </th>
                <th>
                   NIT / Tercero
                </th>
                <th>
                   Saldo inicial
                </th>
                <th>
                   Mov. Débito
                </th>
                <th>
                   Mov. Crédito
                </th>
                <th>
                   Saldo Final
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($grupos_movimientos as $movimientos_grupo)
                @if($agrupar_por_grupo)
                    <?php $grupo = $movimientos_grupo->first()->grupo; ?>
                    <tr class="encabezado-grupo" style="background-color:#e8eaf6;font-weight:bold;">
                        <td colspan="6">Grupo: {{ $grupo->codigo }} {{ $grupo->descripcion }}</td>
                    </tr>
                @endif
                @foreach($movimientos_grupo as $movimiento_cuenta)
                        
                <tr class="fila-{{$j}}" >
                    <td>
                        {{ $movimiento_cuenta->cuenta->codigo }} {{ $movimiento_cuenta->cuenta->descripcion }}
                    </td>
                    <td>
                        @if( $movimiento_cuenta->tercero != null)
                            {{ $movimiento_cuenta->tercero->numero_identificacion }} {{ $movimiento_cuenta->tercero->descripcion }}
                        @endif
                    </td>
                    <td class="text-right">
                    {{ number_format($movimiento_cuenta->saldo_inicial, 0, ',', '.')}}
                    </td>
                    <td class="text-right">
                    {{ number_format($movimiento_cuenta->debitos, 0, ',', '.')}}
                    </td>
                    <td class="text-right">
                    {{ number_format($movimiento_cuenta->creditos, 0, ',', '.')}}
                    </td>
                    <td class="text-right">
                    {{ number_format( $movimiento_cuenta->saldo_final , 0, ',', '.')}}
                    </td>
                </tr>

                <?php
                    $j++;
                    if ($j==3) {
                        $j=1;
                    }
                    $total_debitos += $movimiento_cuenta->debitos;
                    $total_creditos += $movimiento_cuenta->creditos;
                    $total_saldo_inicial += $movimiento_cuenta->saldo_inicial;
                    $total_saldo_final += $movimiento_cuenta->saldo_final;
                ?>
            @endforeach
                @if($agrupar_por_grupo)
                    <tr class="subtotal-grupo" style="background-color:#f5f5f5;font-weight:bold;">
                        <td colspan="2">Subtotal {{ $grupo->codigo }} {{ $grupo->descripcion }}</td>
                        <td class="text-right">{{ number_format($movimientos_grupo->sum('saldo_inicial'), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($movimientos_grupo->sum('debitos'), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($movimientos_grupo->sum('creditos'), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($movimientos_grupo->sum('saldo_final'), 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
            <tr class="fila-{{$j}} total-general">
                <td colspan="2">
                   TOTAL GENERAL
                </td>
                <td class="text-right">
                   {{ number_format($total_saldo_inicial, 0, ',', '.')}}
                </td>
                <td class="text-right">
                   {{ number_format($total_debitos, 0, ',', '.')}}
                </td>
                <td class="text-right">
                   {{ number_format($total_creditos, 0, ',', '.')}}
                </td>
                <td class="text-right">
                   {{ number_format($total_saldo_final, 0, ',', '.')}}
                </td>
            </tr>
        </tbody>
    </table>
</div>
