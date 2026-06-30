<h3>Listado de habitaciones</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Numero</th>
            <th>Tipo</th>
            <th>Producto</th>
            <th>Piso</th>
            <th>Capacidad</th>
            <th>Estado</th>
            <th>Activa</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rooms as $room)
            <tr>
                <td>{{ $room->room_number }}</td>
                <td>{{ $room->room_type }}</td>
                <td>{{ $room->product ? $room->product->descripcion : $room->inv_producto_id }}</td>
                <td>{{ $room->floor }}</td>
                <td class="text-right">{{ $room->capacity }}</td>
                <td>{{ $room->status }}</td>
                <td>{{ $room->is_active ? 'Si' : 'No' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
