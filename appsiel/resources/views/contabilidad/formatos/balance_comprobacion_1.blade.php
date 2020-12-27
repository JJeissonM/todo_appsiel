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
            <?php

        	for ($i=0; $i < count($cuentas_con_movimiento) ; $i++)
        	{ 

                $valores_cuenta = App\Contabilidad\ContabMovimiento::where('contab_movimientos.fecha','>=',$fecha_desde)
                                ->where('contab_movimientos.fecha','<=',$fecha_hasta)
                                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                                ->where('contab_movimientos.contab_cuenta_id','=',$cuentas_con_movimiento[$i]['id'])
                                ->select( DB::raw( 'sum(contab_movimientos.valor_debito) AS debitos' ), DB::raw( 'sum(contab_movimientos.valor_credito) AS creditos' ) )
                                ->get()
                                ->toArray()[0];

                $saldo_inicial_sql = App\Contabilidad\ContabMovimiento::where('contab_movimientos.fecha','<',$fecha_desde)
                                ->where('contab_movimientos.contab_cuenta_id','=',$cuentas_con_movimiento[$i]['id'])
                                ->where('contab_movimientos.core_empresa_id','=',Auth::user()->empresa_id)
                                ->select( DB::raw( 'sum(contab_movimientos.valor_saldo) AS valor_saldo' ) )
                                ->get()
                                ->toArray()[0];

                $saldo_inicial = $saldo_inicial_sql['valor_saldo'];

                $debitos = $valores_cuenta['debitos'];
                $creditos = $valores_cuenta['creditos'];
                $saldo_final = $saldo_inicial + $debitos + $creditos;
            ?>
                        
        	<tr class="fila-{{$i}}" >
                <td>
                   {{ $cuentas_con_movimiento[$i]['codigo']}} {{ $cuentas_con_movimiento[$i]['descripcion']}}
                </td>
                <td>
                   {{ number_format($saldo_inicial, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format($debitos, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format($creditos, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format( $saldo_final , 0, ',', '.')}}
                </td>
            </tr>
             <?php
        		$j++;
        		if ($j==3) {
        		    $j=1;
        		}
        		$total_debitos+=$debitos;
        		$total_creditos+=$creditos;
        		$total_saldo_inicial+=$saldo_inicial;
        		$total_saldo_final+=$saldo_final;
        	} // END FOR
        	?>

            <tr  class="fila-{{$i}}" >
                <td>
                   &nbsp;
                </td>
                <td>
                   {{ number_format($total_saldo_inicial, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format($total_debitos, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format($total_creditos, 0, ',', '.')}}
                </td>
                <td>
                   {{ number_format($total_saldo_final, 0, ',', '.')}}
                </td>
            </tr>
        </tbody>
    </table>
</div>