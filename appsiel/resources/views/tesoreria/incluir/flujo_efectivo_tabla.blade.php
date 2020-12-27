<?php

    /*
            PENDIENTE POR TERMINAR
            SE DEBE QUITAER ESTE CODIGO DEL TesoreriaController
    */
    
    $fecha_desde = $request->fecha_desde;
    $fecha_hasta = $request->fecha_hasta;
    
    $tipo_movimiento = "%".$request->tipo_movimiento."%";
    $core_tercero_id = "%".$request->core_tercero_id."%";

    $saldo_inicial = TesoMovimiento::leftJoin('teso_motivos','teso_motivos.id','=','teso_movimientos.teso_motivo_id')
            ->where('teso_movimientos.fecha','<',$fecha_desde)
            ->select( DB::raw( 'sum(teso_movimientos.valor_movimiento) AS valor_movimiento' ) )
            ->get()
            ->toArray()[0]['valor_movimiento'];
    
    $movimiento_entradas = TesoMovimiento::movimiento_por_tipo_motivo('entrada', $fecha_desde, $fecha_hasta);
    
    $movimiento_salidas = TesoMovimiento::movimiento_por_tipo_motivo('salida', $fecha_desde, $fecha_hasta);

    // 
    $valor_movimiento = 0;
    $j = 0;
    $i = 0;

?>
<h3> Flujo de efectivo </h3>
<table class="table table-striped tabla_registros" style="margin-top: -4px;">
    <thead>
        <tr>
            <th> &nbsp; </th>
            <th> Motivo </th>
            <th> Valor Movimiento </th>
            <th> Saldo </th>
        </tr>
    </thead>
    <tbody>

        <tr class="fila-{{$j}}" >
            <td colspan="3">
            </td>
            <td>
               {{ number_format( $saldo_inicial , 0, ',', '.') }}
            </td>
        </tr>

        <?php $this->saldo = $saldo_inicial; ?>

        {{ $this->seccion_tabla_movimiento('ENTRADAS', $movimiento_entradas, $saldo_inicial) }}

        <?php $gran_total = $this->total_valor_movimiento; ?>

        {{ $this->seccion_tabla_movimiento('SALIDAS', $movimiento_salidas, $this->saldo) }}
        
        <?php $gran_total += $this->total_valor_movimiento; ?>

        <tr  class="fila-{{$j}}" >
            <td colspan="2">
               <b>FLUJO NETO</b>
            </td>
            <td>
               {{ number_format($gran_total, 0, ',', '.') }}
            </td>
            <td>
               {{ number_format($this->saldo, 0, ',', '.') }}
            </td>
        </tr>
    </tbody>
</table>