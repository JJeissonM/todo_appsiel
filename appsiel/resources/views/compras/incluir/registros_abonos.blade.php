@if( !is_null($abonos) )
    <h4 style="text-align: center;"> Pagos aplicados </h4>
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

                        $modelo = App\Sistema\Modelo::find( $transaccion->core_modelo_id );
                        $url_ver = str_replace('id_fila', $el_documento->id, $modelo->url_ver)

                    ?>

                    <td> 
                        <a href="{{ url( $url_ver.'?id='.$transaccion->core_app_id.'&id_modelo='.$modelo->id.'&id_transaccion='.$transaccion->id ) }}" target="_blank" title="{{ $transaccion->descripcion }}"> {{ $doc_encabezado->documento_prefijo_consecutivo }}</a>  
                    </td>
                    <td> {{ $el_documento->fecha }} </td>
                    <td> {{ $el_documento->descripcion }} </td>
                    <td> ${{ number_format( $doc_encabezado->abono, 0, ',', '.') }} </td>
                </tr>
                <?php 
                    $total_abono += $doc_encabezado->abono;
                ?>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">&nbsp;</td>
                <td> ${{ number_format($total_abono, 0, ',', '.') }} </td>
            </tr>
        </tfoot>
    </table>
@endif