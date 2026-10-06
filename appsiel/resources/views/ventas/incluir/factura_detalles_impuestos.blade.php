@if( (int)config('configuracion.liquidacion_impuestos') )
    <div class="container">        
        <div style="text-align: center; widtd: 100%; background: #ddd; font-weight: bold; clear:botd;">
            Impuestos
        </div>

        <table class="tabla_con_bordes">
                <tr>
                    <td>Tipo</td>
                    <td>Vlr. Compra</td>
                    <td>Base</td>
                    <td>Valor</td>
                </tr> 
                @foreach( $array_tasas as $key => $value )
                    <tr>
                        <td> {{ $value['tipo'] }} </td>
                        <td> ${{ number_format( $value['precio_total'], 0, ',', '.') }} </td>
                        <?php 
                            $base = $value['base_impuesto'];
                            /*if( $value['tasa'] == 0 )
                            {
                                $base = 0;
                            }*/
                        ?>
                        <td> ${{ number_format( $base, 0, ',', '.') }} </td>
                        <td> ${{ number_format( $value['valor_impuesto'], 0, ',', '.') }} </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4">
                        &nbsp;
                    </td>
                </tr>
                @if( !is_null($resolucion) )

                <?php
                    //dd($resolucion->toArray());
                ?>
                    <tr>
                        <td colspan="4">
                            @include('ventas.incluir.factura_detalles_resolucion_dian', ['resolucion' => $resolucion])
                        </td>
                    </tr>
                @endif
        </table>
    </div>
@endif