<!DOCTYPE html>
<html>
<head>
    <title> Comprobante Informe Diario </title>

    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: {{ config('ventas_pos.tamanio_fuente_factura') . 'px'  }};
        }

        @page {
          margin: 15px;
          size: {{ config('ventas_pos.ancho_formato_impresion') . 'in' }} 38.5in;
        }

        .page-break {
            page-break-after: always;
        }

        .lbl_doc_anulado{
            background-color: rgba(253, 1, 1, 0.33);
            width: 100%;
            top: 300px;
            transform: rotate(-45deg);
            text-align: center;
            font-size: 2em;
        }

    </style>
</head>
<body>

    <?php
        $tamanino_fuente_2 = '0.9em';
        $primera_linea_movimiento = $movimientos->first();
        $ultima_linea_movimiento = $movimientos->last();
        $empresa = $primera_linea_movimiento->empresa;
        $ciudad = $empresa->ciudad;
    ?>

    <td style="text-align: center;">
        @include('ventas_pos.formatos_impresion.datos_encabezado_factura')
    </td>
    
    <table style="margin-top: 12px !important; font-size: {{ $tamanino_fuente_2 }};" width="100%">
        <tr>
            <td>
                <b>Caja:</b> 000{{ $primera_linea_movimiento->pdv->id }}
            </td>
            <td>
                <b>Fecha:</b>  {{ $primera_linea_movimiento->fecha }} 
            </td>
        </tr>
        <tr>
            <td>
                <b>S/N:</b> -------------
            </td>
            <td>
                <b>Hora:</b>  {{ explode(' ', $ultima_linea_movimiento->created_at)[1] }}
            </td>
        </tr>
    </table>
    
    <?php 
        $movin_por_grupos = $movimientos->groupBy('item_category_id');
    ?>

    <div style="border: solid 1px #ddd; border-radius: 4px; padding: 20px;">
        <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
            <thead>
                <tr>
                    <th>GRUPO</th>
                    <th>IVA</th>
                    <th>CANT.</th>
                    <th>VLR. TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ( $movin_por_grupos as $movin_grupo)
                    <?php
                        $movin_por_tasas_iva = $movin_grupo->groupBy('tasa_impuesto');
                    ?>
                    @foreach ($movin_por_tasas_iva as $movin_tasa)
                        <?php
                            $primera_linea = $movin_tasa->first();
                        ?>
                        <tr>
                            <td>
                                {{ $primera_linea->item_category->descripcion }}
                            </td>
                            <td align="center">
                                {{ $primera_linea->tasa_impuesto }}
                            </td>
                            <td align="center">
                                {{ $movin_tasa->sum('cantidad') }}
                            </td>
                            <td align="right">
                                ${{ number_format($movin_tasa->sum('base_impuesto_total'), 0, ',','.') }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                
                <tr>
                    <td>
                        Impuesto bolsa plástica 
                    </td>
                    <td align="center">
                        &nbsp;
                    </td>
                    <td align="center">
                        0
                    </td>
                    <td align="right">
                        $0
                    </td>
                </tr>
            </tbody>
        </table>        
    </div>
    <br>
    <div style="border: solid 1px #ddd; border-radius: 4px; padding: 15px;">
        <h6>RESUMEN DE LAS VENTAS</h6>
        <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
            <tbody>
                <tr>
                    <td>
                        <b>Total artículos:</b>
                    </td>
                    <td align="right">
                        {{ number_format($movimientos->sum('cantidad'), 0, ',','.') }}
                    </td>
                </tr>
                <?php 
                    $movin_por_tasas_iva = $movimientos->groupBy('tasa_impuesto');
                ?>
                @foreach ($movin_por_tasas_iva as $tasa => $movin_grupo)
                    <?php 
                        if ($tasa == '0')
                            continue;
                    ?>
                    <tr>
                        <td>
                            <b>Base IVA {{ $tasa }}%:</b>
                        </td>
                        <td align="right">
                            ${{ number_format($movin_grupo->sum('base_impuesto_total'), 0, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <b>Valor IVA {{ $tasa }}%:</b>
                        </td>
                        <td align="right">
                            ${{ number_format( $movin_grupo->sum('base_impuesto_total') * (float)$tasa / 100, 0, ',', '.' ) }}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td>
                        <b>Vta. Excenta o Excluida:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $movimientos->where('tasa_impuesto','0')->sum('base_impuesto_total'), 0, ',', '.' ) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Venta Total:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $movimientos->sum('base_impuesto_total'), 0, ',', '.' ) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Impuesto bolsa plástica:</b>
                    </td>
                    <td align="right">
                        $0
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <b>TOTAL:</b>
                    </td>
                    <td align="right">
                        ${{ number_format( $movimientos->sum('base_impuesto_total'), 0, ',', '.' ) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Transacción inicial:</b>
                    </td>
                    <td align="right">
                        {{ $primera_linea_movimiento->consecutivo }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Transacción final:</b>
                    </td>
                    <td align="right">
                        {{ $ultima_linea_movimiento->consecutivo }}
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        <b>Total transacciones:</b>
                    </td>
                    <td align="right">
                        {{ $ultima_linea_movimiento->consecutivo - $primera_linea_movimiento->consecutivo + 1 }}
                    </td>
                </tr>
            </tbody>
        </table>        
    </div>

    <br>
    
    @include('ventas_pos.formatos_impresion.resumen_ventas_por_medio_pago', [ 'fecha' => $primera_linea_movimiento->fecha, 'pdv_id' => $primera_linea_movimiento->pdv->id ] )

</body>

</html>