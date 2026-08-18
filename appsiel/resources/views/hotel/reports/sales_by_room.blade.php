<style>
    .hotel-room-sales-report {
        background: #fff !important;
        color: #000 !important;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 10px;
        line-height: 1.2;
    }

    .hotel-room-sales-title {
        margin: 0 0 4px;
        text-align: center;
        font-size: 16px;
        font-weight: bold;
    }

    .hotel-room-sales-filters {
        margin-bottom: 8px;
        text-align: center;
        color: #000 !important;
        font-size: 9px;
    }

    .hotel-room-sales-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
    }

    .hotel-room-sales-table th,
    .hotel-room-sales-table td {
        background: #fff !important;
        border: 1px solid #000 !important;
        color: #000 !important;
        padding: 4px;
        vertical-align: middle;
    }

    .hotel-room-sales-table thead th {
        background: #fff !important;
        border-bottom: 2px solid #000 !important;
        text-align: center;
        white-space: nowrap;
    }

    .hotel-room-sales-table .hotel-room-summary td {
        background: #fff !important;
        font-weight: bold;
        border-top: 2px solid #000 !important;
    }

    .hotel-room-sales-table .hotel-money {
        text-align: right;
        white-space: nowrap;
    }

    .hotel-room-sales-table .hotel-center {
        text-align: center;
    }

    .hotel-room-sales-table tfoot td {
        background: #fff !important;
        font-size: 11px;
        font-weight: bold;
        border-top: 2px solid #000 !important;
    }

    .hotel-room-sales-expected {
        display: block;
        color: #000 !important;
        font-size: 8px;
        font-weight: normal;
    }

    @media print {
        @page {
            margin: 8mm;
            size: landscape;
        }

        .hotel-room-sales-report {
            font-size: 8px;
        }

        .hotel-room-sales-title {
            font-size: 13px;
        }

        .hotel-room-sales-table th,
        .hotel-room-sales-table td {
            padding: 3px;
        }
    }
</style>

<div class="hotel-room-sales-report">
    <h3 class="hotel-room-sales-title">Reporte de ventas por habitación</h3>
    <div class="hotel-room-sales-filters">
        Período de ventas: {{ date('d/m/Y', strtotime($fechaDesde)) }} al {{ date('d/m/Y', strtotime($fechaHasta)) }}
        &nbsp;|&nbsp; Detalle: {{ $mostrarDetalle ? 'Sí' : 'No' }}
        &nbsp;|&nbsp; IVA incluido: {{ $ivaIncluido ? 'Sí' : 'No' }}
    </div>

    <table id="tbDatos" class="report-table hotel-room-sales-table">
        <thead>
            <tr>
                <th>Habitación</th>
                <th>Estadías</th>
                <th>Huésped</th>
                <th>Check-in</th>
                <th>Check-out</th>
                <th>Días</th>
                <th>Habitación</th>
                <th>Bebidas/Mecatos</th>
                <th>Lavandería y adicional</th>
                <th>Otros</th>
                <th>Total estadía</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rooms as $room)
                <tr class="hotel-room-summary">
                    <td class="hotel-center">{{ $room['room_number'] }}</td>
                    <td class="hotel-center">{{ count($room['stays']) }}</td>
                    <td colspan="4"></td>
                    <td class="hotel-money">$ {{ number_format($room['room_total'], 2, ',', '.') }}</td>
                    <td class="hotel-money">$ {{ number_format($room['beverages_total'], 2, ',', '.') }}</td>
                    <td class="hotel-money">$ {{ number_format($room['laundry_total'], 2, ',', '.') }}</td>
                    <td class="hotel-money">$ {{ number_format($room['others_total'], 2, ',', '.') }}</td>
                    <td class="hotel-money">$ {{ number_format($room['total'], 2, ',', '.') }}</td>
                </tr>

                @if($mostrarDetalle)
                    @foreach($room['stays'] as $stay)
                        <tr class="hotel-stay-detail">
                            <td></td>
                            <td>EST-{{ $stay['stay_id'] }}</td>
                            <td>{{ $stay['guest_name'] }}</td>
                            <td class="hotel-center">{{ $stay['check_in'] }}</td>
                            <td class="hotel-center">
                                {{ $stay['check_out'] }}
                                @if($stay['check_out_expected'])
                                    <span class="hotel-room-sales-expected">Fecha esperada</span>
                                @endif
                            </td>
                            <td class="hotel-center">{{ $stay['days'] }}</td>
                            <td class="hotel-money">$ {{ number_format($stay['room_total'], 2, ',', '.') }}</td>
                            <td class="hotel-money">$ {{ number_format($stay['beverages_total'], 2, ',', '.') }}</td>
                            <td class="hotel-money">$ {{ number_format($stay['laundry_total'], 2, ',', '.') }}</td>
                            <td class="hotel-money">$ {{ number_format($stay['others_total'], 2, ',', '.') }}</td>
                            <td class="hotel-money">$ {{ number_format($stay['total'], 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach

            @if(count($rooms) == 0)
                <tr>
                    <td colspan="11">No hay ventas hoteleras para el rango de fechas seleccionado.</td>
                </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" class="hotel-money">TOTALES</td>
                <td class="hotel-money">$ {{ number_format($grandTotalRoom, 2, ',', '.') }}</td>
                <td class="hotel-money">$ {{ number_format($grandTotalBeverages, 2, ',', '.') }}</td>
                <td class="hotel-money">$ {{ number_format($grandTotalLaundry, 2, ',', '.') }}</td>
                <td class="hotel-money">$ {{ number_format($grandTotalOthers, 2, ',', '.') }}</td>
                <td class="hotel-money">$ {{ number_format($grandTotal, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
</div>
