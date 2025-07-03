@if( (int)config('configuracion.liquidacion_impuestos') )
    <div class="container">        
        <div style="text-align: center; widtd: 100%; background: #ddd; font-weight: bold; clear:botd;">
            Impuestos
        </div>

        <table class="tabla_con_bordes">
                <tr>
                    <td>Tipo</td>
                    <td>Vlr. Compra</td>
                    <td>Base IVA</td>
                    <td>Vlr. IVA</td>
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
                    <tr>
                        <td colspan="4">
                            Factura {{ $resolucion->tipo_solicitud }} por la DIAN. Resolución No. {{ $resolucion->numero_resolucion }} del {{ $resolucion->fecha_expedicion }}. Prefijo {{ $resolucion->prefijo }} desde {{ $resolucion->numero_fact_inicial }} hasta {{ $resolucion->numero_fact_final }}
                        </td>
                    </tr>
                @endif
        </table>
    </div>
@endif