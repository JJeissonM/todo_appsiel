<h3> Documentos de ventas | {{ $pdv->descripcion }}</h3>
<hr>
<table class="table table-striped table-bordered">
    {{ Form::bsTableHeader( ['Fecha', 'Documento', 'Cliente', 'Cond. pago', 'Detalle', 'Valor total', 'Estado', 'Acción'] ) }}
    <tbody>

            <?php  
                $total_ventas = 0;
            ?>

        @foreach ($encabezados_documentos as $fila)
            <tr>

                <?php  
                    $cantidad = count( $fila );
                ?>
                @for( $i=1; $i < $cantidad; $i++)
                    <td class="table-text">
                        {{ $fila['campo'.$i] }}
                    </td>
                @endfor
                    <td>
                        <a class="btn btn-info btn-xs btn-detail" href="{{ url('pos_factura_imprimir/'.$fila['campo8'].'?id=20&id_modelo=230&id_transaccion=47') }}" title="Imprimir" id="btn_print" target="_blank"><i class="fa fa-btn fa-print"></i>&nbsp;</a>
                        &nbsp;&nbsp;&nbsp;
                        <a class="btn btn-primary btn-xs btn-detail" href="{{ url('ventas/'.$fila['campo8'].'?id=20&id_modelo=230&id_transaccion=47') }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>
                        &nbsp;&nbsp;&nbsp;
                        @if( $fila['campo7'] != 'Anulado' )
                            <button class="btn btn-danger btn-xs btn_anular_factura" data-pdv_id="{{ $pdv->id }}" data-doc_encabezado_id="{{$fila['campo8']}}" data-lbl_factura="{{$fila['campo2']}}" title="Anular"> <i class="fa fa-trash"></i> </button>
                        @endif
                    </td>
            </tr>
            <?php
                if( $fila['campo7'] != 'Anulado' )
                { 
                    $total_ventas += $fila['campo6'];
                }
            ?>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5"> Total Venta </td>
            <td> ${{ number_format( $total_ventas, 0, ',','.') }} </td>
            <td colspan="2"> </td>
        </tr>
    </tfoot>
</table>