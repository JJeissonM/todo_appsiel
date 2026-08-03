<?php $hotelUrl = 'App\\Hotel\\Support\\HotelBreadcrumb'; ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Habitacion</th>
                <th>Huesped principal</th>
                <th>Check-in</th>
                <th>Salida esperada</th>
                <th>Estado</th>
                <th>Acciones</th>
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
                    <td>{{ $stay->status }}</td>
                    <td><a href="{{ url($hotelUrl::url('hotel/stays/'.$stay->id)) }}" class="btn btn-info btn-xs">Ver</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
