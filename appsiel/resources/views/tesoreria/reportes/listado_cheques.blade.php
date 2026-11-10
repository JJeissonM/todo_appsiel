<style>
    .teso-listado-cheques {
        color: #111;
        font-family: DejaVu Sans, Arial, sans-serif;
        font-size: 9px;
    }

    .teso-listado-cheques .report-header {
        border-bottom: 2px solid #222;
        margin-bottom: 10px;
        padding-bottom: 7px;
        text-align: center;
    }

    .teso-listado-cheques .report-title {
        font-size: 16px;
        font-weight: bold;
        margin-bottom: 3px;
        text-transform: uppercase;
    }

    .teso-listado-cheques .company-name {
        font-size: 12px;
        font-weight: bold;
    }

    .teso-listado-cheques .report-meta {
        border: 1px solid #777;
        margin-bottom: 10px;
        padding: 6px 8px;
    }

    .teso-listado-cheques .report-table {
        border-collapse: collapse;
        table-layout: fixed;
        width: 100%;
    }

    .teso-listado-cheques .report-table th {
        border-bottom: 1px solid #333;
        border-top: 1px solid #333;
        font-size: 8px;
        padding: 4px 2px;
        text-align: left;
        text-transform: uppercase;
    }

    .teso-listado-cheques .report-table td {
        border-bottom: 1px solid #bbb;
        font-size: 8px;
        padding: 4px 2px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .teso-listado-cheques .report-table tfoot td {
        border-bottom: 1px solid #333;
        border-top: 2px solid #222;
        font-weight: bold;
    }

    .teso-listado-cheques .text-right { text-align: right; }
    .teso-listado-cheques .text-center { text-align: center; }
    .teso-listado-cheques .empty-row { padding: 16px 4px; text-align: center; }
</style>

<div class="teso-listado-cheques">
    <div class="report-header">
        <div class="report-title">Listado de cheques</div>
        @if(!is_null($empresa))
            <div class="company-name">{{ $empresa->descripcion }}</div>
            <div>NIT: {{ $empresa->numero_identificacion }}{{ $empresa->digito_verificacion != '' ? ' - ' . $empresa->digito_verificacion : '' }}</div>
        @endif
    </div>

    <div class="report-meta">
        <strong>Fecha de emisión:</strong> {{ $fecha_desde }} a {{ $fecha_hasta }}
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Tercero:</strong> {{ is_null($tercero) ? 'Todos' : $tercero->descripcion }}
        @if(!is_null($tercero) && $tercero->numero_identificacion != '')
            ({{ $tercero->numero_identificacion }})
        @endif
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <strong>Registros:</strong> {{ count($cheques) }}
    </div>

    <table class="report-table" id="teso_listado_cheques">
        <thead>
            <tr>
                <th style="width: 7%;">Emisión</th>
                <th style="width: 7%;">Cobro</th>
                <th style="width: 8%;">Fuente</th>
                <th style="width: 15%;">Tercero</th>
                <th style="width: 8%;">Número</th>
                <th style="width: 8%;">Referencia</th>
                <th style="width: 10%;">Banco</th>
                <th style="width: 8%;">Doc. origen</th>
                <th style="width: 8%;">Doc. consumo</th>
                <th style="width: 8%;">Estado</th>
                <th style="width: 10%;" class="text-right">Valor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cheques as $cheque)
                <tr>
                    <td>{{ $cheque->fecha_emision }}</td>
                    <td>{{ $cheque->fecha_cobro }}</td>
                    <td>{{ $cheque->fuente == 'propio' ? 'Propio' : 'De tercero' }}</td>
                    <td>
                        {{ $cheque->tercero }}
                        @if($cheque->tercero_identificacion != '')
                            <br>{{ $cheque->tercero_identificacion }}
                        @endif
                    </td>
                    <td>{{ $cheque->numero_cheque }}</td>
                    <td>{{ $cheque->referencia_cheque }}</td>
                    <td>{{ $cheque->entidad_financiera }}</td>
                    <td>{{ $cheque->documento_origen }}</td>
                    <td>{{ $cheque->documento_consumo }}</td>
                    <td>{{ $cheque->estado }}</td>
                    <td class="text-right tableexport-number">{{ number_format($cheque->valor, 2, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="empty-row">No se encontraron cheques para los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-right">TOTAL</td>
                <td class="text-right tableexport-number">{{ number_format($total, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
