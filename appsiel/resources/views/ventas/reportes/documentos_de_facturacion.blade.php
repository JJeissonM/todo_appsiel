<style>
    .vtas-documentos-facturacion-report {
        color: #1f2933;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9px;
        line-height: 1.25;
    }

    .vtas-documentos-facturacion-report .report-header {
        border-bottom: 2px solid #34495e;
        margin-bottom: 10px;
        padding-bottom: 8px;
        text-align: center;
    }

    .vtas-documentos-facturacion-report .report-title {
        color: #1f2933;
        font-size: 17px;
        font-weight: bold;
        margin: 0 0 4px;
        text-transform: uppercase;
    }

    .vtas-documentos-facturacion-report .company-name {
        font-size: 12px;
        font-weight: bold;
        margin-bottom: 2px;
    }

    .vtas-documentos-facturacion-report .company-data {
        color: #52606d;
        font-size: 9px;
    }

    .vtas-documentos-facturacion-report .report-meta {
        background-color: #f3f6f8;
        border: 1px solid #cbd5dc;
        margin: 0 0 10px;
        padding: 6px 8px;
    }

    .vtas-documentos-facturacion-report .report-meta table {
        border-collapse: collapse;
        width: 100%;
    }

    .vtas-documentos-facturacion-report .report-meta td {
        border: none;
        color: #334e68;
        padding: 1px 4px;
        vertical-align: top;
    }

    .vtas-documentos-facturacion-report .meta-label {
        color: #52606d;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
        width: 76px;
    }

    .vtas-documentos-facturacion-report .report-message {
        color: #52606d;
        font-size: 9px;
        margin-bottom: 6px;
    }

    .vtas-documentos-facturacion-report .report-table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    .vtas-documentos-facturacion-report .report-table th {
        background-color: #e8edf1;
        border: 1px solid #9aa6b2;
        color: #1f2933;
        font-size: 8px;
        font-weight: bold;
        padding: 5px 4px;
        text-align: left;
        text-transform: uppercase;
        vertical-align: middle;
    }

    .vtas-documentos-facturacion-report .report-table td {
        border-bottom: 1px solid #d9e2ec;
        color: #243b53;
        font-size: 8px;
        padding: 4px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .vtas-documentos-facturacion-report .report-table tbody tr:nth-child(even) td {
        background-color: #f8fafc;
    }

    .vtas-documentos-facturacion-report .report-table tfoot td {
        background-color: #dfe7ed;
        border-bottom: 1px solid #9aa6b2;
        border-top: 2px solid #34495e;
        color: #102a43;
        font-size: 8px;
        font-weight: bold;
        padding: 6px 4px;
    }

    .vtas-documentos-facturacion-report .text-right {
        text-align: right;
    }

    .vtas-documentos-facturacion-report .text-center {
        text-align: center;
    }

    .vtas-documentos-facturacion-report .transaction-col {
        width: 16%;
    }

    .vtas-documentos-facturacion-report .date-col {
        width: 8%;
    }

    .vtas-documentos-facturacion-report .doc-col {
        width: 9%;
    }

    .vtas-documentos-facturacion-report .id-col {
        width: 10%;
    }

    .vtas-documentos-facturacion-report .client-col {
        width: 24%;
    }

    .vtas-documentos-facturacion-report .money-col {
        width: 11%;
    }

    .vtas-documentos-facturacion-report .product-col {
        width: 19%;
    }

    .vtas-documentos-facturacion-report .qty-col {
        width: 7%;
    }

    .vtas-documentos-facturacion-report .with-products .transaction-col {
        width: 12%;
    }

    .vtas-documentos-facturacion-report .with-products .date-col {
        width: 7%;
    }

    .vtas-documentos-facturacion-report .with-products .doc-col {
        width: 8%;
    }

    .vtas-documentos-facturacion-report .with-products .id-col {
        width: 9%;
    }

    .vtas-documentos-facturacion-report .with-products .client-col {
        width: 16%;
    }

    .vtas-documentos-facturacion-report .with-products .money-col {
        width: 8%;
    }

    .vtas-documentos-facturacion-report .empty-row td {
        color: #697a8a;
        padding: 14px 4px;
        text-align: center;
    }
</style>

@php
    $gran_base_impuesto_total = 0;
    $gran_precio_total = 0;
    $gran_valor_iva = 0;
    $cantidad_documentos = count($documentos_ventas) + count($documentos_ventas_pos);
@endphp

<div class="vtas-documentos-facturacion-report">

<div class="report-header">
    <h1 class="report-title">Documentos de Facturación</h1>
    <div class="company-name">{{ $empresa->descripcion }}</div>
    <div class="company-data">
        {{ config("configuracion.tipo_identificador") }}:
        @if( config("configuracion.tipo_identificador") == 'NIT')
            {{ number_format( $empresa->numero_identificacion, 0, ',', '.') }}
        @else
            {{ $empresa->numero_identificacion }}
        @endif
        - {{ $empresa->digito_verificacion }}
        <br>
        {{ $empresa->direccion1 }}, {{ $empresa->ciudad->descripcion }} | Teléfono(s): {{ $empresa->telefono1 }}
        @if($empresa->pagina_web)
            | {{ $empresa->pagina_web }}
        @endif
    </div>
</div>

<div class="report-meta">
    <table>
        <tr>
            <td class="meta-label">Periodo</td>
            <td>{{ $fecha_desde }} hasta {{ $fecha_hasta }}</td>
            <td class="meta-label">Detalle</td>
            <td>{{ $detalla_productos ? 'Productos' : 'Documentos' }}</td>
            <td class="meta-label">Documentos</td>
            <td class="text-right">{{ number_format($cantidad_documentos, 0, ',', '.') }}</td>
        </tr>
        @if($cliente_filtro != null)
            <tr>
                <td class="meta-label">Cliente</td>
                <td colspan="5">
                    {{ $cliente_filtro->tercero->numero_identificacion }} - {{ $cliente_filtro->tercero->descripcion }}
                </td>
            </tr>
        @endif
    </table>
</div>

@if($mensaje != '')
    <div class="report-message">{!! $mensaje !!}</div>
@endif

<table class="report-table {{ $detalla_productos ? 'with-products' : '' }}">
    <thead>
        <tr>
            <th class="transaction-col">Transacción</th>
            <th class="date-col text-center">Fecha</th>
            <th class="doc-col">Documento</th>
            <th class="id-col">CC/NIT</th>
            <th class="client-col">Cliente</th>
            @if($detalla_productos)
                <th class="product-col">Producto</th>
                <th class="qty-col text-right">Cant.</th>
            @endif
            <th class="money-col text-right">Base IVA</th>
            <th class="money-col text-right">Vlr. IVA</th>
            <th class="money-col text-right">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($documentos_ventas as $documento)
            @if($detalla_productos)
                @foreach($documento->lineas_registros as $linea)
                    @php
                        $signo = $linea->precio_total < 0 ? -1 : 1;
                        $base_linea = abs($linea->base_impuesto_total) * $signo;
                        $valor_iva = $base_linea * $linea->tasa_impuesto / 100;
                        $gran_base_impuesto_total += $base_linea;
                        $gran_precio_total += $linea->precio_total;
                        $gran_valor_iva += $valor_iva;
                    @endphp
                    <tr>
                        <td>{{ $documento->tipo_transaccion->descripcion }}</td>
                        <td class="text-center">{{ $documento->fecha }}</td>
                        <td>{!! $documento->get_label_documento() !!}</td>
                        <td>{{ $documento->cliente->tercero->numero_identificacion }}</td>
                        <td>{{ $documento->cliente->tercero->descripcion }}</td>
                        <td>{{ $linea->item->get_value_to_show() }}</td>
                        <td class="text-right">{{ number_format( $linea->cantidad, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $base_linea, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $valor_iva, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $linea->precio_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                @php
                    $base_impuesto_total = 0;
                    $precio_total = 0;
                    $valor_iva = 0;

                    foreach($documento->lineas_registros as $linea) {
                        $signo = $linea->precio_total < 0 ? -1 : 1;
                        $base_linea = abs($linea->base_impuesto_total) * $signo;
                        $iva_linea = $base_linea * $linea->tasa_impuesto / 100;

                        $base_impuesto_total += $base_linea;
                        $precio_total += $linea->precio_total;
                        $valor_iva += $iva_linea;

                        $gran_base_impuesto_total += $base_linea;
                        $gran_precio_total += $linea->precio_total;
                        $gran_valor_iva += $iva_linea;
                    }
                @endphp
                <tr>
                    <td>{{ $documento->tipo_transaccion->descripcion }}</td>
                    <td class="text-center">{{ $documento->fecha }}</td>
                    <td>{!! $documento->get_label_documento() !!}</td>
                    <td>{{ $documento->cliente->tercero->numero_identificacion }}</td>
                    <td>{{ $documento->cliente->tercero->descripcion }}</td>
                    <td class="text-right">${{ number_format( $base_impuesto_total, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format( $valor_iva, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format( $precio_total, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach

        @foreach($documentos_ventas_pos as $documento)
            @if($detalla_productos)
                @foreach($documento->lineas_registros as $linea)
                    @php
                        $signo = $linea->precio_total < 0 ? -1 : 1;
                        $base_linea = abs($linea->base_impuesto_total) * $signo;
                        $valor_iva = $base_linea * $linea->tasa_impuesto / 100;
                        $gran_base_impuesto_total += $base_linea;
                        $gran_precio_total += $linea->precio_total;
                        $gran_valor_iva += $valor_iva;
                    @endphp
                    <tr>
                        <td>{{ $documento->tipo_transaccion->descripcion }}</td>
                        <td class="text-center">{{ $documento->fecha }}</td>
                        <td>{!! $documento->get_label_documento() !!}</td>
                        <td>{{ $documento->cliente->tercero->numero_identificacion }}</td>
                        <td>{{ $documento->cliente->tercero->descripcion }}</td>
                        <td>{{ $linea->producto->get_value_to_show() }}</td>
                        <td class="text-right">{{ number_format( $linea->cantidad, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $base_linea, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $valor_iva, 0, ',', '.') }}</td>
                        <td class="text-right">${{ number_format( $linea->precio_total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @else
                @php
                    $base_impuesto_total = 0;
                    $precio_total = 0;
                    $valor_iva = 0;

                    foreach($documento->lineas_registros as $linea) {
                        $signo = $linea->precio_total < 0 ? -1 : 1;
                        $base_linea = abs($linea->base_impuesto_total) * $signo;
                        $iva_linea = $base_linea * $linea->tasa_impuesto / 100;

                        $base_impuesto_total += $base_linea;
                        $precio_total += $linea->precio_total;
                        $valor_iva += $iva_linea;

                        $gran_base_impuesto_total += $base_linea;
                        $gran_precio_total += $linea->precio_total;
                        $gran_valor_iva += $iva_linea;
                    }
                @endphp
                <tr>
                    <td>{{ $documento->tipo_transaccion->descripcion }}</td>
                    <td class="text-center">{{ $documento->fecha }}</td>
                    <td>{!! $documento->get_label_documento() !!}</td>
                    <td>{{ $documento->cliente->tercero->numero_identificacion }}</td>
                    <td>{{ $documento->cliente->tercero->descripcion }}</td>
                    <td class="text-right">${{ number_format( $base_impuesto_total, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format( $valor_iva, 0, ',', '.') }}</td>
                    <td class="text-right">${{ number_format( $precio_total, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endforeach

        @if($cantidad_documentos == 0)
            <tr class="empty-row">
                <td colspan="{{ $detalla_productos ? 10 : 8 }}">No hay documentos para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <td colspan="{{ $detalla_productos ? 7 : 5 }}" class="text-right">Totales</td>
            <td class="text-right">${{ number_format( $gran_base_impuesto_total, 0, ',', '.') }}</td>
            <td class="text-right">${{ number_format( $gran_valor_iva, 0, ',', '.') }}</td>
            <td class="text-right">${{ number_format( $gran_precio_total, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

</div>
