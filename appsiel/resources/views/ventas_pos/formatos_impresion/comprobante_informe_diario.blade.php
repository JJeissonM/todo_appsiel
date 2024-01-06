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
                <b>S/N:</b> -------------
            </td>
        </tr>
        <tr>
            <td>
                <b>Fecha:</b>  {{ $ultima_linea_movimiento->fecha }} 
            </td>
            <td>
                <b>Hora:</b>  {{ explode(' ', $ultima_linea_movimiento->created_at)[1] }}
            </td>
        </tr>
        <tr>
            <td>
                <b>No. Doc. inicial:</b>  {{ $primera_linea_movimiento->consecutivo }} 
            </td>
            <td>
                <b>No. Doc. final:</b>  {{ $ultima_linea_movimiento->consecutivo }}
            </td>
        </tr>
    </table>
    
    <?php 
        $movin_por_grupos = $movimientos->groupBy('item_category_id');
    ?>

    <div style="border: solid 1px #ddd; border-radius: 4px;">
        <table width="100%" style=" font-size: {{ $tamanino_fuente_2 }};">
            <thead>
                <th>
                    <td>GRUPO</td>
                    <td>IVA</td>
                    <td>CANT.</td>
                    <td>VLR. TOTAL</td>
                </th>
            </thead>
            <tbody>
                @foreach ( $movin_por_grupos as $movin_grupo)
                    <?php
                        dd($movin_grupo->pluck('tasa_impuesto')->toArray());
                        $movin_por_tasas_iva = $movin_grupo->groupBy('tasa_impuesto');
                    ?>
                    @foreach ($movin_por_tasas_iva as $movin_tasa)
                    <?php
                        dd(movin_tasa);
                    ?>
                        <tr>
                            <td>
                                {{ $movin_tasa->item_category->descripcion }}
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                
            </tbody>
        </table>        
    </div>
</body>

</html>