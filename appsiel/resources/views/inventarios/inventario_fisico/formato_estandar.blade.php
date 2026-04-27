<?php
    $ciudad_empresa = DB::table('core_ciudades')->where('id', $empresa->codigo_ciudad)->first();
    $logo_src = '';
    $logo_path = storage_path('app/logos_empresas/'.$empresa->imagen);

    if ( !empty($empresa->imagen) && file_exists($logo_path) )
    {
        $logo_type = pathinfo($logo_path, PATHINFO_EXTENSION);
        $logo_src = 'data:image/'.$logo_type.';base64,'.base64_encode(file_get_contents($logo_path));
    }

    $total_cantidad = 0;
    $total_documento = 0;
    $total_cantidad_sistema = 0;
    $total_documento_sistema = 0;
    $total_cantidad_dif = 0;
    $total_documento_dif = 0;
?>

<style type="text/css">
    @page {
        margin: 18px 22px;
    }

    body {
        color: #222;
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        line-height: 1.25;
    }

    table {
        border-collapse: collapse;
        width: 100%;
    }

    .header {
        border: 1px solid #d8dde3;
        margin-bottom: 10px;
    }

    .header td {
        vertical-align: middle;
        padding: 10px;
    }

    .company-logo {
        text-align: center;
        width: 18%;
    }

    .company-logo img {
        max-height: 58px;
        max-width: 105px;
    }

    .company-logo-empty {
        border: 1px solid #d8dde3;
        color: #999;
        font-size: 8px;
        height: 50px;
        line-height: 50px;
        margin: 0 auto;
        text-align: center;
        width: 85px;
    }

    .company-info {
        text-align: center;
        width: 42%;
    }

    .company-name {
        font-size: 13px;
        font-weight: bold;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .doc-info {
        border-left: 1px solid #d8dde3;
        width: 40%;
    }

    .doc-title {
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 8px;
        text-align: center;
    }

    .info-table td {
        padding: 2px 0;
    }

    .label {
        color: #555;
        font-weight: bold;
        white-space: nowrap;
        width: 76px;
    }

    .subheader {
        border: 1px solid #d8dde3;
        margin-bottom: 9px;
    }

    .subheader td {
        padding: 6px 10px;
        vertical-align: top;
    }

    .note {
        color: #555;
        font-size: 9px;
        margin-bottom: 4px;
    }

    .inventory-table {
        table-layout: fixed;
    }

    .inventory-table th {
        background: #4fb892;
        border: 1px solid #43a681;
        color: #111;
        font-size: 8px;
        font-weight: bold;
        padding: 5px 3px;
        text-align: center;
        text-transform: uppercase;
    }

    .inventory-table td {
        border: 1px solid #d9dfe5;
        padding: 4px 3px;
        vertical-align: middle;
    }

    .inventory-table tbody tr:nth-child(odd) td {
        background: #f3f5f6;
    }

    .inventory-table tfoot td {
        background: #edf7f3;
        border-top: 2px solid #4fb892;
        font-weight: bold;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .product {
        font-size: 8.5px;
        line-height: 1.18;
    }

    .status {
        border-radius: 2px;
        display: inline-block;
        font-size: 8px;
        font-weight: bold;
        padding: 2px 4px;
        text-align: center;
    }

    .status-missing {
        background: #fde7e7;
        color: #c51616;
    }

    .status-extra {
        background: #e4f7e9;
        color: #0d7a31;
    }

    .status-ok {
        background: #eeeeee;
        color: #333333;
    }

    .detail {
        border-top: 1px solid #d8dde3;
        color: #444;
        margin-top: 10px;
        padding-top: 7px;
    }
</style>

<table class="header">
    <tr>
        <td class="company-logo">
            @if( $logo_src != '' )
                <img src="{{ $logo_src }}">
            @else
                <div class="company-logo-empty">Logo</div>
            @endif
        </td>
        <td class="company-info">
            <div class="company-name">{!! $empresa->descripcion !!}</div>
            <div>
                <b>{{ config("configuracion.tipo_identificador") }}:</b>
                @if( config("configuracion.tipo_identificador") == 'NIT')
                    {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}
                @else
                    {{ $empresa->numero_identificacion }}
                @endif
                - {{ $empresa->digito_verificacion }}
            </div>
            <div>{!! $empresa->direccion1 !!}@if( $ciudad_empresa != null ), {!! $ciudad_empresa->descripcion !!}@endif</div>
            <div><b>Telefono(s):</b> {!! $empresa->telefono1 !!}</div>
        </td>
        <td class="doc-info">
            <div class="doc-title">{{ $doc_encabezado->documento_transaccion_descripcion }}</div>
            <table class="info-table">
                <tr>
                    <td class="label">Documento:</td>
                    <td>{{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha:</td>
                    <td>{{ date_format(date_create($doc_encabezado->fecha), "d-m-Y") }}</td>
                </tr>
                <tr>
                    <td class="label">Estado:</td>
                    <td>{{ $doc_encabezado->estado }}</td>
                </tr>
                <tr>
                    <td class="label">Bodega:</td>
                    <td>{{ $doc_encabezado->bodega_descripcion }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table class="subheader">
    <tr>
        <td width="55%">
            <table class="info-table">
                <tr>
                    <td class="label">Tercero:</td>
                    <td>{{ $doc_encabezado->tercero_nombre_completo }}</td>
                </tr>
                <tr>
                    <td class="label">{{ config("configuracion.tipo_identificador") }}:</td>
                    <td>
                        @if( config("configuracion.tipo_identificador") == 'NIT')
                            {{ number_format( $doc_encabezado->numero_identificacion, 0, ',', '.') }}
                        @else
                            {{ $doc_encabezado->numero_identificacion }}
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td>
            <table class="info-table">
                <tr>
                    <td class="label">Hora inicio:</td>
                    <td>{{ $doc_encabezado->hora_inicio ? date('g:i a', strtotime($doc_encabezado->hora_inicio)) : '' }}</td>
                </tr>
                <tr>
                    <td class="label">Hora fin:</td>
                    <td>{{ $doc_encabezado->hora_finalizacion ? date('g:i a', strtotime($doc_encabezado->hora_finalizacion)) : '' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="note"><b>IF</b> = Inventario Fisico</div>

<table class="inventory-table">
    <thead>
        <tr>
            <th style="width: 5%;">Cod.</th>
            <th style="width: 25%;">Producto</th>
            <th style="width: 8%;">Cant. IF</th>
            <th style="width: 8%;">Costo Unit. IF</th>
            <th style="width: 10%;">Costo Tot. IF</th>
            <th style="width: 9%;">Cant. Sistema</th>
            <th style="width: 10%;">Costo Tot. Sistema</th>
            <th style="width: 8%;">Dif. Cant.</th>
            <th style="width: 9%;">Dif. Costo</th>
            <th style="width: 8%;">Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($doc_registros as $linea )
            <?php
                $descripcion_item = $linea->descripcion_item ?? $linea->producto_descripcion;
                $diferencia = round( $linea->cantidad - $linea->cantidad_sistema, 2 );
                $diferencia_costo = $linea->costo_total - $linea->costo_total_sistema;
                $estado = 'Faltante';
                $estado_clase = 'status-missing';

                if ( $diferencia > 0 )
                {
                    $estado = 'Sobrante';
                    $estado_clase = 'status-extra';
                }

                if ( -0.0001 < $diferencia && $diferencia < 0.0001 )
                {
                    $estado = 'OK';
                    $estado_clase = 'status-ok';
                    $diferencia_costo = 0;
                }

                $costo_unit_conteo = 0;
                if ($linea->cantidad != 0) {
                    $costo_unit_conteo = $linea->costo_total / $linea->cantidad;
                }

                $total_cantidad += $linea->cantidad;
                $total_documento += $linea->costo_total;
                $total_cantidad_sistema += $linea->cantidad_sistema;
                $total_documento_sistema += $linea->costo_total_sistema;
                $total_cantidad_dif += $diferencia;
                $total_documento_dif += $diferencia_costo;
            ?>
            <tr>
                <td class="text-center">{{ $linea->producto_id }}</td>
                <td class="product">{{ $descripcion_item }}</td>
                <td class="text-right">{{ number_format( $linea->cantidad, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format( $costo_unit_conteo, 0, ',', '.') }}</td>
                <td class="text-right">${{ number_format( $linea->costo_total, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format( $linea->cantidad_sistema, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format( $linea->costo_total_sistema, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format( $diferencia, 2, ',', '.') }}</td>
                <td class="text-right">${{ number_format( $diferencia_costo, 0, ',', '.') }}</td>
                <td class="text-center"><span class="status {{ $estado_clase }}">{{ $estado }}</span></td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <?php
            $estado_total = 'Faltante';
            $estado_total_clase = 'status-missing';

            if ( $total_cantidad_dif > 0 )
            {
                $estado_total = 'Sobrante';
                $estado_total_clase = 'status-extra';
            }

            if ( -0.0001 < $total_cantidad_dif && $total_cantidad_dif < 0.0001 )
            {
                $estado_total = 'OK';
                $estado_total_clase = 'status-ok';
                $total_documento_dif = 0;
            }
        ?>
        <tr>
            <td colspan="2" class="text-right">Totales</td>
            <td class="text-right">{{ number_format($total_cantidad, 2, ',', '.') }}</td>
            <td></td>
            <td class="text-right">${{ number_format($total_documento, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($total_cantidad_sistema, 2, ',', '.') }}</td>
            <td class="text-right">${{ number_format($total_documento_sistema, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($total_cantidad_dif, 2, ',', '.') }}</td>
            <td class="text-right">${{ number_format($total_documento_dif, 0, ',', '.') }}</td>
            <td class="text-center"><span class="status {{ $estado_total_clase }}">{{ $estado_total }}</span></td>
        </tr>
    </tfoot>
</table>

<div class="detail">
    <b>Detalle:</b><br>
    {!! $doc_encabezado->descripcion !!}
</div>
