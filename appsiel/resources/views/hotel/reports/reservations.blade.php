<h3>Reservas activas</h3>
<p>Total de reservas: {{ count($reservations) }}. Las fechas filtran las reservas que coinciden con el período seleccionado.</p>
<table class="table table-bordered table-striped">
    <thead><tr><th>ID</th><th>Habitación</th><th>Cliente</th><th>Identificación</th><th>Teléfono</th><th>Email</th><th>Desde</th><th>Hasta</th><th>Estado</th><th>Observaciones</th></tr></thead>
    <tbody>
    @forelse($reservations as $reservation)
        <tr>
            <td>{{ $reservation->id }}</td>
            <td>{{ $reservation->room ? $reservation->room->room_number : $reservation->room_id }}</td>
            <td>{{ $reservation->cliente && $reservation->cliente->tercero ? $reservation->cliente->tercero->descripcion : $reservation->cliente_id }}</td>
            <td>{{ $reservation->cliente && $reservation->cliente->tercero ? $reservation->cliente->tercero->numero_identificacion : '' }}</td>
            <td>{{ $reservation->cliente && $reservation->cliente->tercero ? $reservation->cliente->tercero->telefono1 : '' }}</td>
            <td>{{ $reservation->cliente && $reservation->cliente->tercero ? $reservation->cliente->tercero->email : '' }}</td>
            <td>{{ $reservation->reserved_from }}</td><td>{{ $reservation->reserved_until }}</td>
            <td>{{ $reservation->status }}</td><td>{{ $reservation->notes }}</td>
        </tr>
    @empty
        <tr><td colspan="10">No hay reservas activas para los filtros seleccionados.</td></tr>
    @endforelse
    </tbody>
</table>
