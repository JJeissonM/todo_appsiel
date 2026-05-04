<h4 style="width: 100%; text-align: center;">Balance de inventarios</h4>
<h5 style="width: 100%; text-align: center;">
    del <code>{{ $fecha_desde }}</code> al <code>{{ $fecha_hasta }}</code>
    @if( $inv_bodega_id != '' )
    &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; <b> Bodega: </b> {{ \App\Inventarios\InvBodega::find(
    $inv_bodega_id )->descripcion }}
    @endif
</h5>
<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        <?php
            $movimientos_salidas_por_motivo_aux = collect( $movimientos_salidas_por_motivo->toArray() );
            $motivos_salidas = $movimientos_salidas_por_motivo_aux->unique('motivo_id')->values();
            $encabezado_tabla = ['Cód.','Producto','S. Inicial','Entradas'];

            if ( $separar_salidas_por_motivo )
            {
                foreach ( $motivos_salidas as $motivo_salida )
                {
                    $encabezado_tabla[] = $motivo_salida['motivo_descripcion'];
                }
            }else{
                $encabezado_tabla[] = 'Salidas';
            }

            $encabezado_tabla[] = 'S. Final';
        ?>
        {{ Form::bsTableHeader($encabezado_tabla) }}
        <tbody>
            <?php 
                $saldos_items_aux = collect( $saldos_items->toArray() );
                $movimientos_entradas_aux = collect( $movimientos_entradas->toArray() );
                $movimientos_salidas_aux = collect( $movimientos_salidas->toArray() );

                $total_saldo_ini = 0;
                $total_entradas =   0;
                $total_salidas =   0;
                $total_salidas_por_motivo = [];
                $total_saldo_fin = 0;

            ?>
            @foreach( $items AS $item )
            <?php 
                    $saldo_ini = $saldos_items_aux->where( 'item_id', $item->id )->pluck('cantidad_total_movimiento')->first();
                    
                    $entradas = $movimientos_entradas_aux->where( 'item_id', $item->id )->pluck('cantidad_total_movimiento')->first();
                    
                    $salidas = $movimientos_salidas_aux->where( 'item_id', $item->id )->pluck('cantidad_total_movimiento')->first();

                    $saldo_fin = $saldo_ini + $entradas + $salidas;

                    $mostrar_item = 1;
                    if( !$mostrar_items_sin_movimiento )
                    {
                        if( $saldo_ini == 0 && $entradas == 0 && $salidas == 0 )
                        {
                            $mostrar_item = 0;
                        }
                    }

                ?>

                @if( $mostrar_item )
                    <tr>
                        <td class="text-center">{{ $item->id }}</td>
                        <td>{{ $item->descripcion }} ({{ $item->get_unidad_medida1() }})</td>
                        <td class="text-right">{{ number_format($saldo_ini, 2, ',', '.') }} </td>
                        <td class="text-right">{{ number_format( $entradas, 2, ',', '.') }}</td>
                        @if( $separar_salidas_por_motivo )
                            @foreach( $motivos_salidas as $motivo_salida )
                                <?php
                                    $salida_motivo = $movimientos_salidas_por_motivo_aux->where( 'item_id', $item->id )->where( 'motivo_id', $motivo_salida['motivo_id'] )->pluck('cantidad_total_movimiento')->first();
                                    $salida_motivo = (float)$salida_motivo;
                                    $total_salidas_por_motivo[$motivo_salida['motivo_id']] = (float)(isset($total_salidas_por_motivo[$motivo_salida['motivo_id']]) ? $total_salidas_por_motivo[$motivo_salida['motivo_id']] : 0) + $salida_motivo;
                                ?>
                                <td class="text-right">{{ number_format( $salida_motivo, 2, ',', '.') }}</td>
                            @endforeach
                        @else
                            <td class="text-right">{{ number_format( $salidas, 2, ',', '.') }}</td>
                        @endif
                        <td class="text-right">{{ number_format( $saldo_fin, 2, ',', '.') }}</td>
                    </tr>
                    <?php 
                        $total_saldo_ini += $saldo_ini;
                        $total_entradas += $entradas;
                        $total_salidas += $salidas;
                        $total_saldo_fin += $saldo_fin; 
                    ?>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2"></td>
                <td class="text-right"><b>{{ number_format( $total_saldo_ini, 2, ',', '.') }} </b> </td>
                <td class="text-right"><b>{{ number_format( $total_entradas, 2, ',', '.') }} </b></td>
                @if( $separar_salidas_por_motivo )
                    @foreach( $motivos_salidas as $motivo_salida )
                        <td class="text-right"><b>{{ number_format( isset($total_salidas_por_motivo[$motivo_salida['motivo_id']]) ? $total_salidas_por_motivo[$motivo_salida['motivo_id']] : 0, 2, ',', '.') }} </b></td>
                    @endforeach
                @else
                    <td class="text-right"><b>{{ number_format( $total_salidas, 2, ',', '.') }} </b></td>
                @endif
                <td class="text-right"><b>{{ number_format( $total_saldo_fin, 2, ',', '.') }} </b></td>
            </tr>
        </tfoot>
    </table>
</div>
