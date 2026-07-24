<h3>Libro de Huéspedes</h3>

<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Check-in</th>
            <th>Nombres y Apellidos</th>
            <th>Número identificación</th>
            <th>Fecha de nacimiento</th>
            <th>Nacionalidad</th>
            <th>Tipo documento</th>
            <th>Ocupación</th>
            <th>Procedencia</th>
            <th>Destino</th>
            <th>Check-out</th>
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
                <td>{{ $row->check_in_at }}</td>
                <td>{{ $nombre }}</td>
                <td>{{ $row->numero_identificacion }}</td>
                <td>{{ $row->fecha_nacimiento }}</td>
                <td>{{ $row->nacionalidad }}</td>
                <td>{{ $row->tipo_documento }}</td>
                <td>{{ $row->ocupacion }}</td>
                <td>{{ $procedencia }}</td>
                <td>{{ $destino }}</td>
                <td>{{ $checkOut }}</td>
            </tr>
        @endforeach

        @if(count($rows) == 0)
            <tr>
                <td colspan="10">No hay registros para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
</table>
