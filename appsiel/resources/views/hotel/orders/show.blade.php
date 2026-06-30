@extends('layouts.principal')

@section('content')
    {{ Form::bsMigaPan($miga_pan) }}
    @include('layouts.mensajes')

    <div class="container-fluid">
        <div class="marco_formulario">
            <div class="row">
                <div class="col-md-8">
                    <h3>Pedido hotelero {{ $order->document_number }}</h3>
                </div>
                <div class="col-md-4 text-right">
                    <a href="{{ url('hotel/stays/'.$order->stay_id) }}" class="btn btn-default btn-sm">Volver a estadia</a>
                </div>
            </div>
            <table class="table table-bordered">
                <tr><th>Cliente</th><td>{{ $order->cliente && $order->cliente->tercero ? $order->cliente->tercero->descripcion : $order->cliente_id }}</td></tr>
                <tr><th>Habitacion</th><td>{{ $order->stay && $order->stay->room ? $order->stay->room->room_number : '' }}</td></tr>
                <tr><th>Fecha</th><td>{{ $order->order_date }}</td></tr>
                <tr><th>Estado</th><td>{{ $order->status }}</td></tr>
                <tr><th>Factura</th><td>{{ $order->invoice_type }} {{ $order->sales_doc_id ? 'Ventas #'.$order->sales_doc_id : '' }} {{ $order->pos_doc_id ? 'POS #'.$order->pos_doc_id : '' }}</td></tr>
            </table>

            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <form method="POST" action="{{ url('hotel/orders/'.$order->id.'/generate-standard-invoice') }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <button class="btn btn-success" onclick="return confirm('Generar factura estandar?')">Generar factura estandar</button>
                </form>
                <form method="POST" action="{{ url('hotel/orders/'.$order->id.'/generate-pos-invoice') }}" style="display:inline-block;">
                    {{ csrf_field() }}
                    <button class="btn btn-primary" onclick="return confirm('Generar factura POS?')">Generar factura POS</button>
                </form>
            @endif
        </div>

        <div class="marco_formulario">
            <h4>Lineas del pedido</h4>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripcion</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Descuento</th>
                            <th>Impuesto</th>
                            <th>Total</th>
                            <th>Origen</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $total = 0; ?>
                        @foreach($order->lines as $line)
                            <?php $total += $line->line_total; ?>
                            <tr>
                                <form method="POST" action="{{ url('hotel/orders/'.$order->id.'/lines/'.$line->id.'/update') }}">
                                    {{ csrf_field() }}
                                    <td>{{ $line->product ? $line->product->descripcion : $line->producto_id }}</td>
                                    <td><input type="text" name="description" class="form-control input-sm" value="{{ $line->description }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="quantity" class="form-control input-sm text-right" value="{{ $line->quantity }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="unit_price" class="form-control input-sm text-right" value="{{ $line->unit_price }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="discount" class="form-control input-sm text-right" value="{{ $line->discount }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td><input type="text" name="tax_value" class="form-control input-sm text-right" value="{{ $line->tax_value }}" {{ $order->status != App\Hotel\HotelOrderHeader::STATUS_ABIERTO ? 'disabled' : '' }}></td>
                                    <td class="text-right">{{ number_format($line->line_total, 2, ',', '.') }}</td>
                                    <td>{{ $line->source_type }}</td>
                                    <td>
                                        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                                            <button class="btn btn-warning btn-xs">Actualizar</button>
                                        @endif
                                </form>
                                        @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                                            <form method="POST" action="{{ url('hotel/orders/'.$order->id.'/lines/'.$line->id.'/delete') }}" style="display:inline-block;">
                                                {{ csrf_field() }}
                                                <button class="btn btn-danger btn-xs" onclick="return confirm('Eliminar linea?')">Eliminar</button>
                                            </form>
                                        @endif
                                    </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th colspan="6" class="text-right">Total</th>
                            <th class="text-right">{{ number_format($total, 2, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if($order->status == App\Hotel\HotelOrderHeader::STATUS_ABIERTO)
                <h4>Agregar consumo</h4>
                <form method="POST" action="{{ url('hotel/orders/'.$order->id.'/lines') }}">
                    {{ csrf_field() }}
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label>Producto</label>
                                <select name="producto_id" class="form-control" required>
                                    @foreach($products as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Cantidad</label>
                                <input type="text" name="quantity" class="form-control" value="1">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Precio</label>
                                <input type="text" name="unit_price" class="form-control" placeholder="Automatico">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Descripcion</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="source_type" value="MANUAL">
                    <button class="btn btn-primary">Agregar linea</button>
                </form>
            @endif
        </div>
    </div>
@endsection
