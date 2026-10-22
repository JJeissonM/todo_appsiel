<h3>Estadías x Cobrar</h3>
<p>Pedidos abiertos pendientes de facturar. Las fechas filtran la fecha del pedido. Los anticipos/abonos disponibles pertenecen al cliente y se descuentan una sola vez, aunque tenga varias estadías. El saldo es estimado hasta aplicar los anticipos; no incluye facturas ya emitidas.</p>
@forelse($customers as $customer)
    <h4>{{ $customer['name'] }}</h4>
    <table class="table table-bordered table-striped">
        <thead><tr><th>Pedido / Documento</th><th>Fecha</th><th>Estadía</th><th>Habitación</th><th>Total pedido</th><th>Anticipo/abono disponible</th></tr></thead>
        <tbody>
        @foreach($customer['orders'] as $row)
            <tr>
                <td>{{ $row['order']->document_number ?: 'PED-' . $row['order']->id }}</td>
                <td>{{ $row['order']->order_date }}</td>
                <td>{{ $row['order']->stay_id }}</td>
                <td>{{ $row['order']->stay && $row['order']->stay->room ? $row['order']->stay->room->room_number : '' }}</td>
                <td class="text-right">{{ number_format($row['total'], 2, ',', '.') }}</td><td></td>
            </tr>
        @endforeach
        @foreach($customer['advances'] as $advance)
            <tr>
                <td>{{ $advance->tipo_documento_app ? $advance->tipo_documento_app->prefijo : '' }} {{ $advance->consecutivo }}</td>
                <td>{{ $advance->fecha }}</td><td colspan="2">Anticipo/abono del cliente</td><td></td>
                <td class="text-right">{{ number_format(abs($advance->saldo_pendiente), 2, ',', '.') }}</td>
            </tr>
        @endforeach
            <tr><th colspan="4">Totales del cliente</th><th class="text-right">{{ number_format($customer['total'], 2, ',', '.') }}</th><th class="text-right">{{ number_format($customer['advance_total'], 2, ',', '.') }}</th></tr>
            <tr><th colspan="5">Saldo pendiente estimado por cobrar</th><th class="text-right">{{ number_format($customer['balance'], 2, ',', '.') }}</th></tr>
        </tbody>
    </table>
@empty
    <p>No hay pedidos pendientes para los filtros seleccionados.</p>
@endforelse
<table class="table table-bordered">
    <tr><th>Total pedidos</th><th>Anticipos/abonos disponibles</th><th>Saldo pendiente estimado por cobrar</th></tr>
    <tr><td>{{ number_format($totals['orders'], 2, ',', '.') }}</td><td>{{ number_format($totals['advances'], 2, ',', '.') }}</td><td>{{ number_format($totals['balance'], 2, ',', '.') }}</td></tr>
</table>
