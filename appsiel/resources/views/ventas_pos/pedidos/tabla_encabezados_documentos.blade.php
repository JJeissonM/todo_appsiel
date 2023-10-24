<h3> Pedidos de ventas </h3>
<hr>
<div class="table-responsive">
    <table class="table table-striped table-bordered">
        {{ Form::bsTableHeader( [ 'Acción', 'Fecha', 'Documento', 'Cliente', 'Cond. pago', 'Detalle', 'Valor total', 'Estado'] ) }}
        <tbody>

                <?php  
                    $total_ventas = 0;
                ?>

            @foreach ($encabezados_documentos as $fila)
                <tr>                    
                    <td>
                        <a class="btn btn-primary btn-xs btn-detail" href="{{ url('vtas_pedidos/' . $fila->id .'?id=20&id_modelo=175&id_transaccion=42') }}" title="Consultar" id="btn_print" target="_blank"><i class="fa fa-btn fa-eye"></i>&nbsp;</a>                        
                        
                        &nbsp;&nbsp;&nbsp;
                        
                        @if( $fila->estado != 'Anulado' && $fila->estado == 'Pendiente' && $view != 'index')
                            
                            <a class="btn btn-warning btn-xs btn_modificar_factura" href="{{ url( 'pos_pedido/' .   $fila->id  . '/edit?id=20&id_modelo=175&id_transaccion=42&action=edit&pdv_id=' . $pdv_id ) }}" title="Modificar"> <i class="fa fa-edit"></i> </a>
                                
                            &nbsp;&nbsp;&nbsp;
                        
                        @endif
                    </td>
                    <td class="table-text">
                        {{ $fila->fecha }}
                    </td>
                    <td class="table-text">
                        {{ $fila->get_label_documento() }}
                    </td>
                    <td class="table-text">
                        {{ $fila->cliente->tercero->descripcion }}
                    </td>
                    <td class="table-text">
                        {{ $fila->forma_pago }}
                    </td>
                    <td class="table-text">
                        {{ $fila->descripcion }}
                    </td>
                    <td class="table-text">
                        ${{ number_format($fila->valor_total, 0, ',', '.') }}
                    </td>
                    <td class="table-text">
                        {{ $fila->estado }}
                    </td>
                </tr>
                <?php
                    if( $fila->estado != 'Anulado' )
                    { 
                        $total_ventas += $fila->valor_total;
                    }
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"> Total pedidos </td>
                <td class="text-tight">
                        ${{ number_format( $total_ventas, 0, ',','.') }}
                </td>
                <td colspan="2"> </td>
            </tr>
        </tfoot>
    </table>
</div>