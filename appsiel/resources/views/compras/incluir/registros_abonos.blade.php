@if( !is_null($abonos) )
    <div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Pagos aplicados</div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Documento','Fecha','Detalle','Abono']) }}
            <tbody>
                <?php 
                
                $total_abono = 0;

                ?>
                @foreach($abonos as $doc_encabezado )
                    <tr>

                        <?php 
                        
                            $transaccion = App\Sistema\TipoTransaccion::find( $doc_encabezado->core_tipo_transaccion_id );
                            $el_documento = app( $transaccion->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                                                ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                                                ->where('consecutivo',$doc_encabezado->consecutivo)
                                                ->get()
                                                ->first();
                                                
                            
                            if ($el_documento == null) {
                                continue;
                            }

                            $prefix_url = 'tesoreria/pagos_cxp/';
                            $id_modelo = 150; // Pagos de CxC
                            switch ( $doc_encabezado->core_tipo_transaccion_id ) {
                                case '9': // Notas de contabilidad
                                    $prefix_url = 'contabilidad/';
                                    $id_modelo = 47; // Documentos contables 
                                    break;
                                
                                case '17': // Pagos generarles de tesorería
                                    $prefix_url = 'tesoreria/pagos/';
                                    $id_modelo = 54; // Pagos de tesorería
                                    break;
                                
                                default:
                                    # code...
                                    break;
                            }

                        ?>

                        <td class="text-center"> 
                            <a href="{{ url( $prefix_url . $el_documento->id . '?id='.$transaccion->core_app_id.'&id_modelo=' . $id_modelo . '&id_transaccion='.$transaccion->id ) }}" target="_blank" title="{{ $transaccion->descripcion }}"> {{ $doc_encabezado->documento_prefijo_consecutivo }}</a>  
                        </td>
                        <td> {{ $el_documento->fecha }} </td>
                        <td> {{ $el_documento->descripcion }} </td>
                        <td class="text-right"> ${{ number_format( $doc_encabezado->abono, 0, ',', '.') }} </td>
                    </tr>
                    <?php 
                        $total_abono += $doc_encabezado->abono;
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