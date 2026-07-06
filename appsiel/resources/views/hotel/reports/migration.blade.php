<h3>Reporte migracion hotelera</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Cod. Hotel</th>
            <th>Cod. Ciudad</th>
            <th>Tipo de documento</th>
            <th>Núm. identificación</th>
            <th>Cod. nacionalidad</th>
            <th>Primer apellido</th>
            <th>Segundo apellido</th>
            <th>Nombre del extranjero</th>
            <th>Tipo de movimiento</th>
            <th>Fecha del movimiento</th>
            <th>Lugar de procedencia</th>
            <th>Lugar de destino</th>
            <th>Fecha de nacimiento</th>
            <th>Habitación</th>
            <th>Estadía</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rows as $row)
            <?php
                $nombre = trim($row->nombre1 . ' ' . $row->otros_nombres);
                if ($nombre == '') {
                    $nombre = $row->descripcion;
                }
            ?>
            <tr>
                <td>{{ $codigoHotel }}</td>
                <td>{{ $row->codigo_ciudad }}</td>
                <td>{{ $row->tipo_documento }}</td>
                <td>{{ $row->numero_identificacion }}</td>
                <td>{{ $row->codigo_nacionalidad }}</td>
                <td>{{ $row->apellido1 }}</td>
                <td>{{ $row->apellido2 }}</td>
                <td>{{ $nombre }}</td>
                <td>{{ $tipoMovimiento }}</td>
                <td>{{ substr($row->check_in_at, 0, 10) }}</td>
                <td>{{ $lugarProcedencia }}</td>
                <td>{{ $lugarDestino }}</td>
                <td>{{ $row->fecha_nacimiento }}</td>
                <td>{{ $row->room_number }}</td>
                <td>#{{ $row->stay_id }}</td>
            </tr>
        @endforeach

        @if(count($rows) == 0)
            <tr>
                <td colspan="15">No hay registros para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
</table>
