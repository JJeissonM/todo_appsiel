<!DOCTYPE html>
<html>
<head>
    <title> Comprobante Informe Diario </title>

    <style type="text/css">
        body{
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
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

    @if( empty($data_by_items->toArray()) )
        <h3>No hay movimiento de ventas en la fecha seleccionada.</h3>
    @else
        
        <?php
            $tamanino_fuente_2 = '0.9em';
        ?>

        <!-- 
        @ include('ventas_pos.formatos_impresion.datos_encabezado_factura')
        -->
        
        <h3 style="width: 100%; text-align:center;"> Resúmen Diario de Ventas</h3>

        <table style="margin-top: 12px !important; font-size: {{ $tamanino_fuente_2 }};" width="100%">
            <tr>
                <td>
                    <b>Fecha:</b>  {{ $fecha_corte }}
                </td>
                <td>
                    &nbsp;
                </td>
            </tr>
        </table>

        <div style="border: solid 1px #ddd; border-radius: 4px; padding: 10px;">
            <?php
                $gran_total_venta = 0;
                $gran_total_cantidad = 0;
                $gran_total_descuento = 0;
            ?>
            @foreach ( $data_by_items as $movin_grupo)
                    <?php 
                        //dd($movin_grupo);
                    ?>
                <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
                    <thead>
                        <tr>
                            <th colspan="5">{{ $movin_grupo['prefijo']->descripcion }}</th>
                        </tr>
                        <tr style="background-color: #ddd !important;">
                            <th style="width: 30%;">ITEM</th>
                            <th>CANT.</th>
                            <th>PRECIO PROM.</th>
                            <th>VLR. TOTAL</th>
                            <th>DCTO. TOTAL</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $items_movim = $movin_grupo['items'];
                            $total_venta = 0;
                            $total_cantidad = 0;
                            $total_descuento = 0;
                        ?>
                        @foreach ( $items_movim as $movim_line )
                            <tr>
                                <td>
                                    {{ $movim_line['item']->get_value_to_show(true) }}
                                </td>
                                <td align="center">
                                    {{ $movim_line['cantidad'] }}
                                </td>
                                <td align="center">
                                    <?php 
                                        $precio_promedio = 0;
                                        if ( $movim_line['cantidad'] != 0 ) {
                                            $precio_promedio = $movim_line['precio_total'] / $movim_line['cantidad'];
                                        }
                                        
                                        $total_venta += $movim_line['precio_total'];
                                        $total_cantidad += $movim_line['cantidad'];
                                        $total_descuento += $movim_line['valor_total_descuento'];
                                    ?>
                                    ${{ number_format($precio_promedio, 0, ',','.') }}
                                </td>
                                <td align="center">
                                    ${{ number_format($movim_line['precio_total'], 0, ',','.') }}
                                </td>
                                <td align="center">
                                    ${{ number_format($movim_line['valor_total_descuento'], 0, ',','.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold;">
                            <td>TOTAL:</td>
                            <td align="center">
                                {{ number_format($total_cantidad, 0, ',', '.') }}
                            </td>
                            <td> &nbsp; </td>
                            <td align="center">
                                ${{ number_format($total_venta, 0, ',', '.') }}
                            </td>
                            <td align="center">
                                ${{ number_format($total_descuento, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    <?php
                        $gran_total_venta += $total_venta;
                        $gran_total_cantidad += $total_cantidad;
                        $gran_total_descuento += $total_descuento;
                    ?>
                </table> 
                <br>
            @endforeach

            <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
                <thead>
                    <tr>
                        <th colspan="5">GRAN TOTAL</th>
                    </tr>
                    <tr style="background-color: #ddd !important;">
                        <th style="width: 30%; color:#ddd;"> ........ </th>
                        <th>CANT.</th>
                        <th style="color:#ddd;"> .... </th>
                        <th>VLR. TOTAL</th>
                        <th>DCTO. TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr style="font-weight: bold;">
                        <td style="color: white;"> ........ </td>
                        <td align="center">
                            {{ number_format($gran_total_cantidad, 0, ',', '.') }}
                        </td>
                        <td style="color: white;"> ........ </td>
                        <td align="center">
                            ${{ number_format($gran_total_venta, 0, ',', '.') }}
                        </td>
                        <td align="center">
                            ${{ number_format($gran_total_descuento, 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table> 
        </div>
    <br>
    
    @include('ventas_pos.formatos_impresion.resumen_ventas_por_caja_banco', [ 'fecha' => $fecha_corte, 'pdv_id' => $pdv_id ] )
    @endif
</body>

</html>