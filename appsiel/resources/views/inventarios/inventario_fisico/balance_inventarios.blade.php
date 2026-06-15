<link rel="stylesheet" href="{{ asset("css/stylepdf.css") }}">

<style type="text/css">
    body {
        font-size: 12px;
    }

    h3, h4 {
        text-align: center;
        margin: 4px 0;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .text-danger {
        color: #c00000 !important;
    }

    .resumen {
        width: 100%;
        text-align: center;
        font-size: 15px;
        margin: 8px 0 12px 0;
    }

    .tabla-balance {
        width: 100%;
        border-collapse: collapse;
    }

    .tabla-balance th {
        background-color: #d2d2d2 !important;
        color: #000 !important;
        border: 1px solid #777;
        padding: 6px 4px;
    }

    .tabla-balance thead tr {
        background-color: #d2d2d2 !important;
        color: #000 !important;
    }

    .tabla-balance thead th,
    .tabla-balance thead th *,
    .tabla-balance thead tr th,
    .tabla-balance thead tr th * {
        color: #000 !important;
    }

    .tabla-balance td {
        border-bottom: 1px solid #ddd;
        padding: 6px 4px;
    }

    .tabla-balance tfoot td {
        font-weight: bold;
        border-top: 2px solid #777;
        background-color: #fff;
    }
</style>

<table style="width: 100%; border-collapse: collapse;">
    <tr>
        <td width="55%" style="border: none;">
            <div class="headempresa">
                @include( 'core.dis_formatos.plantillas.banner_logo_datos_empresa', [ 'vista' => 'show' ] )
            </div>
        </td>
        <td style="border: none;">
            <div class="headdoc">
                <b style="font-size: 1.5em; text-align: center; display: block;">{{ $doc_encabezado->documento_transaccion_descripcion }}</b>
                <br/>
                <b>Documento:</b> {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
                <br/>
                <b>Fecha:</b> {{ $doc_encabezado->fecha }}
                <br/>
                <b>Bodega:</b> {{ $doc_encabezado->bodega_descripcion }}
            </div>
        </td>
    </tr>
</table>

<h3>Balance de inventarios</h3>

@php
    $motivos_entradas = $datos_balance['motivos_entradas'] ?? [];
    $motivos_salidas = $datos_balance['motivos_salidas'] ?? [];
@endphp

<table class="tabla-balance">
    <thead>
        <tr>
            <th style="width: 6%;">Cód.</th>
            <th>Producto</th>
            <th>S. Inicial</th>
            @foreach( $motivos_entradas as $motivo_entrada )
                <th>{{ $motivo_entrada->descripcion }}</th>
            @endforeach
            @foreach( $motivos_salidas as $motivo_salida )
                <th>{{ $motivo_salida->descripcion }}</th>
            @endforeach
            <th>S. Final</th>
            <th>Cant. IF</th>
            <th>Dif.</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach( $datos_balance['items'] as $item )
            <tr>
                <td class="text-center">{{ $item->id }}</td>
                <td>{{ $item->descripcion }} ({{ $item->unidad_medida1 }})</td>
                <td class="text-right">{{ number_format($item->saldo_ini, 2, ',', '.') }}</td>
                @foreach( $motivos_entradas as $motivo_entrada )
                    <td class="text-right">{{ number_format($item->entradas_por_motivo[$motivo_entrada->id] ?? 0, 2, ',', '.') }}</td>
                @endforeach
                @foreach( $motivos_salidas as $motivo_salida )
                    @if( $motivo_salida->id == (int)config('inventarios.ai_motivo_salida_id') )
                    
                        <td class="text-right text-danger">{{ number_format($item->salidas_por_motivo[$motivo_salida->id] ?? 0, 2, ',', '.') }}</td>
                    @else                    
                        <td class="text-right">{{ number_format($item->salidas_por_motivo[$motivo_salida->id] ?? 0, 2, ',', '.') }}</td>
                    @endif
                @endforeach
                <td class="text-right">{{ number_format($item->saldo_fin, 2, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->cantidad_if, 2, ',', '.') }}</td>
                <td class="text-right{{ $item->diferencia < 0 ? ' text-danger' : '' }}">{{ number_format($item->diferencia, 2, ',', '.') }}</td>
                <td>&nbsp;</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="2"></td>
            <td class="text-right">{{ number_format($datos_balance['totales']->saldo_ini, 2, ',', '.') }}</td>
            @foreach( $motivos_entradas as $motivo_entrada )
                <td class="text-right">{{ number_format($datos_balance['totales']->entradas_por_motivo[$motivo_entrada->id] ?? 0, 2, ',', '.') }}</td>
            @endforeach
            @foreach( $motivos_salidas as $motivo_salida )
                <td class="text-right">{{ number_format($datos_balance['totales']->salidas_por_motivo[$motivo_salida->id] ?? 0, 2, ',', '.') }}</td>
            @endforeach
            <td class="text-right">{{ number_format($datos_balance['totales']->saldo_fin, 2, ',', '.') }}</td>
            <td class="text-right">{{ number_format($datos_balance['totales']->cantidad_if, 2, ',', '.') }}</td>
            <td class="text-right{{ $datos_balance['totales']->diferencia < 0 ? ' text-danger' : '' }}">{{ number_format($datos_balance['totales']->diferencia, 2, ',', '.') }}</td>
            <td>&nbsp;</td>
        </tr>
    </tfoot>
</table>

<br>
<b>Detalle: &nbsp;&nbsp;</b>
<br>
{!! $doc_encabezado->descripcion !!}
