@if( !is_null($notas_credito) )
    <div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Notas crédito</div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            {{ Form::bsTableHeader(['Documento','Fecha','Detalle','Valor total']) }}
            <tbody>
                <?php 
                
                $total_valor_total = 0;

                dd($notas_credito);

                ?>
                @foreach($notas_credito as $doc_encabezado )
                    <tr>

                        <?php 
                            
                            $transaccion = App\Sistema\TipoTransaccion::find( $doc_encabezado->core_tipo_transaccion_id );
                            
                            if($transaccion == null)
                            {
                                continue;
                            }
                            $el_documento = app( $transaccion->modelo_encabezados_documentos )->where('core_tipo_transaccion_id',$doc_encabezado->core_tipo_transaccion_id)
                                                ->where('core_tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)
                                                ->where('consecutivo',$doc_encabezado->consecutivo)
                                                ->get()
                                                ->first();
                            
                            if($el_documento == null)
                            {
                                continue;
                            }
                            $modelo = App\Sistema\Modelo::find( $transaccion->core_modelo_id );
                            $url_ver = str_replace('id_fila', $el_documento->id, $modelo->url_ver)

                        ?>

                        <td class="tet-center"> 
                            <a href="{{ url( $url_ver.'?id='.$transaccion->core_app_id.'&id_modelo='.$modelo->id.'&id_transaccion='.$transaccion->id ) }}" target="_blank" title="{{ $transaccion->descripcion }}"> {{ $doc_encabezado->documento_prefijo_consecutivo }}</a>  
                        </td>
                        <td> {{ $el_documento->fecha }} </td>
                        <td> {{ $el_documento->descripcion }} </td>
                        <td class="text-right"> ${{ number_format( $doc_encabezado->valor_total, 0, ',', '.') }} </td>
                    </tr>
                    <?php 
                        $total_valor_total += $doc_encabezado->valor_total;
                    ?>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3">&nbsp;</td>
                    <td class="text-right"> ${{ number_format($total_valor_total, 0, ',', '.') }} </td>
                </tr>
            </tfoot>
        </table>
    </div>
@endif