<h3>Listado de estadias</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Habitacion</th>
            <th>Huesped principal</th>
            <th>Check-in</th>
            <th>Salida esperada</th>
            <th>Check-out</th>
            <th>Huespedes</th>
            <th>Estado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($stays as $stay)
            <tr>
                <td>{{ $stay->id }}</td>
                <td>{{ $stay->room ? $stay->room->room_number : $stay->room_id }}</td>
                <td>{{ $stay->mainGuest && $stay->mainGuest->tercero ? $stay->mainGuest->tercero->descripcion : $stay->main_cliente_id }}</td>
                <td>{{ $stay->checkInAtDisplay() }}</td>
                <td>{{ $stay->expectedCheckOutAtDisplay() }}</td>
                <td>{{ $stay->checkOutAtDisplay() }}</td>
                <td class="text-right">{{ $stay->total_guests }}</td>
                <td>{{ $stay->status }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
