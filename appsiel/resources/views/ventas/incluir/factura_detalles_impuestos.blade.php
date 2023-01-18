@if( (int)config('configuracion.liquidacion_impuestos') )
    <div style="text-align: center; width: 100%; background: #ddd; font-weight: bold;">Detalle de impuestos</div>
    <div class="table-responsive">
        <table style="width: 100%;" class="table table-bordered">
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Vlr. Compra</th>
                    <th>Base IVA</th>
                    <th>Vlr. IVA</th>
                </tr>            
            </thead>
            <tbody>
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
                <tr>
                    <td colspan="4">
                        Esta factura se asimila en todos sus efectos a una Letra de Cambio según Art. 774 del Código de Comercio.
                    </td>
                </tr>

            </tbody>
        </table>
    </div>
@endif