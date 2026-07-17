<?php

    $tiempo_a_liquidar = [
        '105'=>'Una Quincena (105 horas)',
        '110'=>'Una Quincena (110 horas)',
        '117'=>'Una Quincena (117.5 horas)',
        '115'=>'Una Quincena (115 horas)',
        '120'=>'Una Quincena (120 horas)',
        '210'=>'Un mes (210 horas)',
        '220'=>'Un mes (220 horas)',
        '230'=>'Un mes (230 horas)',
        '240'=>'Un mes (240 horas)',
        '9999'=>'Órdenes de trabajo'
    ];
    $color = 'red';

    if ( $encabezado_doc->estado == 'Activo' )
    {
        $color = 'green';
    }

    $es_impresion_nomina = isset($vista) && $vista == 'imprimir';
    $tamano_letra_encabezado = $tamano_letra_encabezado ?? 12;
    $tamano_letra_titulo = $tamano_letra_titulo ?? ($tamano_letra_encabezado * 1.35);
    $alto_logo_formato_1 = $alto_logo_formato_1 ?? null;
    $ancho_logo_formato_1 = $ancho_logo_formato_1 ?? null;
?>
@if($es_impresion_nomina)
<style>
    .nomina-formato-1-encabezado {
        border-collapse: collapse;
        margin: 0 0 2px 0 !important;
        table-layout: fixed;
        width: 100%;
    }

    .nomina-formato-1-encabezado td {
        border: solid 1px #ddd;
        line-height: 1.08;
        padding: 2px 4px !important;
        vertical-align: middle;
    }

    .nomina-formato-1-resumen {
        border-collapse: collapse;
        margin: 0 0 4px 0 !important;
        table-layout: fixed;
        width: 100%;
    }

    .nomina-formato-1-resumen td {
        border: 0;
        line-height: 1.05;
        padding: 1px 6px !important;
    }

    .nomina-formato-1-resumen .detalle {
        padding-top: 3px !important;
    }
</style>
@endif
<table class="{{ $es_impresion_nomina ? 'nomina-formato-1-encabezado' : 'table table-bordered' }}" style="{{ $es_impresion_nomina ? 'font-size: '.$tamano_letra_encabezado.'px;' : '' }}">
    <tr>
        <td style="{{ $es_impresion_nomina ? '' : 'border: solid 1px #ddd; margin-top: -40px;' }}" width="70%">
            @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir', 'doc_encabezado' => $encabezado_doc, 'tamano_letra' => $es_impresion_nomina ? $tamano_letra_encabezado : null, 'alto_logo' => $es_impresion_nomina ? $alto_logo_formato_1 : null, 'ancho_logo' => $es_impresion_nomina ? $ancho_logo_formato_1 : null ] )
        </td>
        <td style="{{ $es_impresion_nomina ? '' : 'border: solid 1px #ddd; padding-top: -20px; font-size: 12px' }}">

            <b style="font-size: {{ $es_impresion_nomina ? $tamano_letra_titulo.'px' : '1.6em' }}; text-align: center; display: block; line-height: 1.05;">{{ $descripcion_transaccion }}</b>
                <b>Documento:</b> {{ $encabezado_doc->documento_app }}
                <br/>

                @php
                    $fecha = explode("-", (string) $encabezado_doc->fecha);
                    if (count($fecha) === 3) {
                        $fecha_texto = $fecha[2].' de '.Form::NombreMes([$fecha[1]]).' de '.$fecha[0];
                    } else {
                        $fecha_texto = $encabezado_doc->fecha;
                    }
                    $tiempo_key = (string) intval($encabezado_doc->tiempo_a_liquidar);
                    $tiempo_texto = $tiempo_a_liquidar[$tiempo_key] ?? $encabezado_doc->tiempo_a_liquidar;
                @endphp

                <b>Fecha: </b> &nbsp; {{ $fecha_texto }}

                <br/>
                <b>Liquidación:</b> {{ $tiempo_texto }}

                <div>
                    <b> Estado: </b> <i class="fa fa-circle" style="color: {{$color}}"> </i> {{ $encabezado_doc->estado }}
                </div>

        </td>
    </tr>
</table>

<table class="{{ $es_impresion_nomina ? 'nomina-formato-1-resumen' : 'table table-bordered' }}" style="{{ $es_impresion_nomina ? 'font-size: '.$tamano_letra_encabezado.'px;' : '' }}">
    <tr>
        <td colspan="3" class="detalle">
            <b>Detalle: </b> &nbsp; {{ $encabezado_doc->descripcion }}
        </td>
    </tr>
    <tr>
        <td>
            <b>Total Devengos: </b> &nbsp; ${{ number_format( $encabezado_doc->total_devengos, '0','.',',') }}
        </td>
        <td>
            <b>Total Deducciones: </b> &nbsp; ${{ number_format( $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
        <td>
            <b>Valor Neto: </b> &nbsp; ${{ number_format( $encabezado_doc->total_devengos - $encabezado_doc->total_deducciones, '0','.',',') }}
        </td>
    </tr>
</table>
