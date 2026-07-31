<style>
    .hotel-guest-book-title {
        margin: 0 0 10px 0;
        text-align: center;
        font-size: 16px;
        font-weight: bold;
    }

    .hotel-guest-book-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 10px;
    }

    .hotel-guest-book-table th,
    .hotel-guest-book-table td {
        border: 1px solid #777;
        padding: 4px;
        vertical-align: top;
        word-wrap: break-word;
    }

    .hotel-guest-book-table th {
        background: #51b99b;
        color: #000;
        text-align: center;
        font-weight: bold;
    }

    .hotel-guest-book-table .hotel-date {
        width: 8%;
    }

    .hotel-guest-book-table .hotel-room {
        width: 6%;
        text-align: center;
    }

    .hotel-guest-book-table .hotel-name {
        width: 15%;
    }

    .hotel-guest-book-table .hotel-id {
        width: 10%;
    }

    .hotel-guest-book-table .hotel-birth {
        width: 8%;
    }

    .hotel-guest-book-table .hotel-country {
        width: 9%;
    }

    .hotel-guest-book-table .hotel-doc {
        width: 9%;
    }

    .hotel-guest-book-table .hotel-job {
        width: 7%;
    }

    .hotel-guest-book-table .hotel-place {
        width: 10%;
    }

    @media print {
        .hotel-guest-book-title {
            font-size: 14px;
        }

        .hotel-guest-book-table {
            font-size: 8px;
        }

        .hotel-guest-book-table th,
        .hotel-guest-book-table td {
            padding: 3px;
        }
    }
</style>

<h3 class="hotel-guest-book-title">Libro de Huéspedes</h3>

<table id="tbDatos" class="table table-bordered table-striped report-table hotel-guest-book-table" style="width:100%; border-collapse:collapse; table-layout:fixed; font-size:10px;">
    <thead>
        <tr>
            <th class="hotel-date" style="width:8%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Check-in</th>
            <th class="hotel-room" style="width:6%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Habitación</th>
            <th class="hotel-name" style="width:15%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Nombres y Apellidos</th>
            <th class="hotel-id" style="width:10%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Número identificación</th>
            <th class="hotel-birth" style="width:8%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Fecha nacimiento</th>
            <th class="hotel-country" style="width:9%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Nacionalidad</th>
            <th class="hotel-doc" style="width:9%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Tipo documento</th>
            <th class="hotel-job" style="width:7%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Ocupación</th>
            <th class="hotel-place" style="width:10%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Procedencia</th>
            <th class="hotel-place" style="width:10%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Destino</th>
            <th class="hotel-date" style="width:8%; border:1px solid #777; padding:4px; background:#51b99b; color:#000; text-align:center;">Check-out</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <?php
                $nombre = trim($row->nombre1 . ' ' . $row->otros_nombres . ' ' . $row->apellido1 . ' ' . $row->apellido2);
                if ($nombre == '') {
                    $nombre = $row->descripcion;
                }
                $procedencia = $lugarProcedencia != '' ? $lugarProcedencia : $row->hotel_procedencia;
                $destino = $lugarDestino != '' ? $lugarDestino : $row->hotel_destino;
                $checkOut = $row->check_out_at != '' ? $row->check_out_at : $row->expected_check_out_at;
            ?>
            <tr>
                <td style="width:8%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->check_in_at }}</td>
                <td style="width:6%; border:1px solid #777; padding:4px; vertical-align:top; text-align:center;">{{ $row->room_number }}</td>
                <td style="width:15%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $nombre }}</td>
                <td style="width:10%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->numero_identificacion }}</td>
                <td style="width:8%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->fecha_nacimiento }}</td>
                <td style="width:9%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->nacionalidad }}</td>
                <td style="width:9%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->tipo_documento }}</td>
                <td style="width:7%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $row->ocupacion }}</td>
                <td style="width:10%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $procedencia }}</td>
                <td style="width:10%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $destino }}</td>
                <td style="width:8%; border:1px solid #777; padding:4px; vertical-align:top;">{{ $checkOut }}</td>
            </tr>
        @endforeach

        @if(count($rows) == 0)
            <tr>
                <td colspan="11" style="border:1px solid #777; padding:4px;">No hay registros para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
</table>
