<?php

    $tiempo_a_liquidar = [
        '110'=>'Una Quincena (110 horas)',
        '117'=>'Una Quincena (117.5 horas)',
        '115'=>'Una Quincena (115 horas)',
        '120'=>'Una Quincena (120 horas)',
        '240'=>'Un mes (240 horas)',
        '230'=>'Un mes (230 horas)',
        '220'=>'Un mes (220 horas)',
        '9999'=>'Órdenes de trabajo'
    ];
    $color = 'red';

    if ( $encabezado_doc->estado == 'Activo' )
    {
        $color = 'green';
    }
?>
<table class="table table-bordered">
    <tr>
        <td style="border: solid 1px #ddd; margin-top: -40px;" width="70%">
            @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'imprimir', 'doc_encabezado' => $encabezado_doc ] )
        </td>
        <td style="border: solid 1px #ddd; padding-top: -20px; font-size: 12px">

            <b style="font-size: 1.6em; text-align: center; display: block;">{{ $descripcion_transaccion }}</b>
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

<table class="table table-bordered">
    <tr>
        <td colspan="3">
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
