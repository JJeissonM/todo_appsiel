
<div class="table-responsive">
    <table style="font-size: 15px; border: 1px solid; border-collapse: collapse;" border="1" width="100%" id="myTable">
        <thead>
            <tr>
                <th colspan="9"><h3>Movimientos de inventarios por Motivo</h3></th>
            </tr>
            <tr>
                <th>Fecha</th>
                <th>Documento</th>
                <th>Tercero</th>
                <th>Motivo</th>
                <th>Bodega</th>
                <th>Producto</th>
                <th>Cant.</th>
                <th>Costo Unit.</th>
                <th>Costo Tot.</th>
            </tr>
        </thead>
        <?php 
            $total_cantidad=0;
            $total_costo_total=0;
        ?>
        @foreach($movements AS $movement_line)
            <?php 
                if ($movement_line->cantidad == 0) {
                    continue;
                }
            ?>
            <tr>
                <td> {{ $movement_line->fecha }} </td>
                <td> {!! $movement_line->enlace_show_documento() !!} </td>
                <td> 
                    @if($movement_line->tercero != null)
                        {{ $movement_line->tercero->descripcion }} 
                    @endif
                </td>
                <td> 
                    @if($movement_line->motivo != null)
                        {{ $movement_line->motivo->descripcion }} 
                    @endif
                </td>
                <td> 
                    @if($movement_line->bodega != null)
                        {{ $movement_line->bodega->descripcion }} 
                    @endif
                </td>
                <td> 
                    @if($movement_line->producto != null)
                        {{ $movement_line->producto->descripcion }} ({{ $movement_line->producto->unidad_medida1 }}) 
                    @endif
                </td>
                <td>{{ number_format($movement_line->cantidad, 2, ',', '.') }} </td>
                <td>{{ '$'.number_format( $movement_line->costo_unitario, 2, ',', '.') }}</td>
                <td>{{ '$'.number_format( $movement_line->costo_total, 2, ',', '.') }}</td>
            </tr>
            <?php 
                $total_cantidad+= $movement_line->cantidad;
                $total_costo_total+= $movement_line->costo_total;
            ?>
        @endforeach
        <tr>
            <td colspan="6"> &nbsp; </td>            
            <td> {{ number_format($total_cantidad, 2, ',', '.') }} </td>
            <td> &nbsp; </td>
            <td> {{ number_format($total_costo_total, 2, ',', '.') }} </td>
        </tr>
    </table>
</div>
    