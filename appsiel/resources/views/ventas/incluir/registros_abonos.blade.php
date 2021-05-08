@if( !is_null($abonos) )
    <div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Abonos aplicados</div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Documento','Fecha','Detalle','Abono']) }}
            <tbody>
                <?php 
                
                $total_abono = 0;

                ?>
                @foreach($abonos as $linea )
                    <tr>

                        <?php 
                
                            $el_documento = app( App\Sistema\TipoTransaccion::find( $linea->core_tipo_transaccion_id )->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$linea->core_tipo_transaccion_id)
                            ->where('core_tipo_doc_app_id',$linea->core_tipo_doc_app_id)
                            ->where('consecutivo',$linea->consecutivo)
                            ->get()->first();

                        ?>

                        <td class="text-center"> 
                            <a href="{{ url('tesoreria/recaudos_cxc/'.$el_documento->id.'?id=3&id_modelo=153&id_transaccion=32') }}" target="_blank"> {{ $linea->documento_prefijo_consecutivo }}</a>  
                        </td>
                        <td> {{ $el_documento->fecha }} </td>
                        <td> {{ $el_documento->descripcion }} </td>
                        <td class="text-right"> ${{ number_format( $linea->abono, 0, ',', '.') }} </td>
                    </tr>
                    <?php 
                        $total_abono += $linea->abono;
                    ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-right"> ${{ number_format($total_abono, 0, ',', '.') }} </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif