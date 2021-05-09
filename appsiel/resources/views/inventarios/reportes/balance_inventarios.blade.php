<h4 style="width: 100%; text-align: center;">Balance de inventarios</h4>
<h5 style="width: 100%; text-align: center;"> 
    del <code>{{ $fecha_desde }}</code> al <code>{{ $fecha_hasta }}</code>
    @if( $inv_bodega_id != '' )
        &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; <b> Bodega: </b> {{ \App\Inventarios\InvBodega::find( $inv_bodega_id )->descripcion }}
    @endif
 </h5>
<div class="table-responsive">
    <table id="myTable" class="table table-striped">
        {{ Form::bsTableHeader(['Cód.','Producto','S. Inicial','Entradas','Salidas','S. Final']) }}
        <tbody>
            <?php 
                $saldos_items_aux = collect( $saldos_items->toArray() );
                $movimientos_entradas_aux = collect( $movimientos_entradas->toArray() );
                $movimientos_salidas_aux = collect( $movimientos_salidas->toArray() );
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
                        <td>{{ $item->descripcion }} ({{ $item->unidad_medida1 }})</td>
                        <td class="text-right">{{ number_format($saldo_ini, 2, ',', '.') }} </td>
                        <td class="text-right">{{ number_format( $entradas, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format( $salidas, 2, ',', '.') }}</td>
                        <td class="text-right">{{ number_format( $saldo_fin, 2, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
    