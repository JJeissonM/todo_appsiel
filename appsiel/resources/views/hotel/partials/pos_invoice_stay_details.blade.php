<?php
    $hotel_orders_invoice_details = array();
    $hotel_module_enabled_value = strtolower((string)env('HOTEL_MODULE_ENABLED', false));
    $hotel_module_enabled = in_array($hotel_module_enabled_value, array('1', 'true', 'yes', 'on'));
    $hotel_pos_doc_id = 0;
    $hotel_sales_doc_id = 0;
    $hotel_route_transaccion_id = (int)\Illuminate\Support\Facades\Input::get('id_transaccion');

    if (isset($doc_encabezado) && !is_null($doc_encabezado) && isset($doc_encabezado->id)) {
        if ($hotel_route_transaccion_id > 0 && $hotel_route_transaccion_id != 47) {
            $hotel_sales_doc_id = (int)$doc_encabezado->id;

            if (isset($doc_encabezado->ventas_doc_relacionado_id) && (int)$doc_encabezado->ventas_doc_relacionado_id > 0) {
                $hotel_pos_doc_id = (int)$doc_encabezado->ventas_doc_relacionado_id;
            }
        } elseif (isset($doc_encabezado->core_tipo_transaccion_id) && (int)$doc_encabezado->core_tipo_transaccion_id != 47) {
            $hotel_sales_doc_id = (int)$doc_encabezado->id;

            if (isset($doc_encabezado->ventas_doc_relacionado_id) && (int)$doc_encabezado->ventas_doc_relacionado_id > 0) {
                $hotel_pos_doc_id = (int)$doc_encabezado->ventas_doc_relacionado_id;
            }
        } else {
            $hotel_pos_doc_id = (int)$doc_encabezado->id;
        }
    }

    if ($hotel_pos_doc_id <= 0 && $hotel_sales_doc_id <= 0 && isset($datos_factura) && !is_null($datos_factura) && isset($datos_factura->id)) {
        if ($hotel_route_transaccion_id > 0 && $hotel_route_transaccion_id != 47) {
            $hotel_sales_doc_id = (int)$datos_factura->id;
        } else {
            $hotel_pos_doc_id = (int)$datos_factura->id;
        }
    }

    if ($hotel_pos_doc_id <= 0 && $hotel_sales_doc_id <= 0 && isset($id)) {
        if ($hotel_route_transaccion_id > 0 && $hotel_route_transaccion_id != 47) {
            $hotel_sales_doc_id = (int)$id;
        } else {
            $hotel_pos_doc_id = (int)$id;
        }
    }

    if (($hotel_pos_doc_id > 0 || $hotel_sales_doc_id > 0) && $hotel_module_enabled && \Illuminate\Support\Facades\Schema::hasTable('hotel_order_headers') && \Illuminate\Support\Facades\Schema::hasTable('hotel_stays') && \Illuminate\Support\Facades\Schema::hasTable('hotel_rooms')) {
        $hotel_orders = array();
        $hotel_sales_doc = null;
        $hotel_doc_for_pos_lookup = null;

        if (isset($doc_encabezado) && !is_null($doc_encabezado) && isset($doc_encabezado->id)) {
            $hotel_doc_for_pos_lookup = $doc_encabezado;
        }

        if ($hotel_sales_doc_id > 0 && \Illuminate\Support\Facades\Schema::hasTable('vtas_doc_encabezados')) {
            $hotel_sales_doc = \App\Ventas\VtasDocEncabezado::find($hotel_sales_doc_id);

            if (!is_null($hotel_sales_doc)) {
                $hotel_doc_for_pos_lookup = $hotel_sales_doc;

                if ($hotel_pos_doc_id <= 0 && isset($hotel_sales_doc->ventas_doc_relacionado_id) && (int)$hotel_sales_doc->ventas_doc_relacionado_id > 0) {
                    $hotel_pos_doc_id = (int)$hotel_sales_doc->ventas_doc_relacionado_id;
                }
            }
        }

        if ($hotel_sales_doc_id > 0) {
            $hotel_orders = \App\Hotel\HotelOrderHeader::where('sales_doc_id', $hotel_sales_doc_id)
                ->with(array('stay.room', 'stay.mainGuest.tercero'))
                ->orderBy('id')
                ->get();
        }

        if (count($hotel_orders) == 0 && $hotel_pos_doc_id > 0) {
            $hotel_orders = \App\Hotel\HotelOrderHeader::where('pos_doc_id', $hotel_pos_doc_id)
                ->with(array('stay.room', 'stay.mainGuest.tercero'))
                ->orderBy('id')
                ->get();
        }

        if (count($hotel_orders) == 0 && $hotel_sales_doc_id > 0 && !is_null($hotel_doc_for_pos_lookup) && \Illuminate\Support\Facades\Schema::hasTable('vtas_pos_doc_encabezados')) {
            $hotel_pos_doc = \App\VentasPos\FacturaPos::where('core_empresa_id', $hotel_doc_for_pos_lookup->core_empresa_id)
                ->where('core_tipo_transaccion_id', $hotel_doc_for_pos_lookup->core_tipo_transaccion_id)
                ->where('core_tipo_doc_app_id', $hotel_doc_for_pos_lookup->core_tipo_doc_app_id)
                ->where('consecutivo', $hotel_doc_for_pos_lookup->consecutivo)
                ->orderBy('id', 'DESC')
                ->first();

            if (!is_null($hotel_pos_doc)) {
                $hotel_orders = \App\Hotel\HotelOrderHeader::where('pos_doc_id', $hotel_pos_doc->id)
                    ->with(array('stay.room', 'stay.mainGuest.tercero'))
                    ->orderBy('id')
                ->get();
            }
        }

        if (count($hotel_orders) == 0 && $hotel_sales_doc_id > 0 && !is_null($hotel_doc_for_pos_lookup) && \Illuminate\Support\Facades\Schema::hasTable('vtas_pos_doc_encabezados')) {
            $hotel_pos_doc = \App\VentasPos\FacturaPos::where('core_empresa_id', $hotel_doc_for_pos_lookup->core_empresa_id)
                ->where('core_tipo_doc_app_id', $hotel_doc_for_pos_lookup->core_tipo_doc_app_id)
                ->where('consecutivo', $hotel_doc_for_pos_lookup->consecutivo)
                ->orderBy('id', 'DESC')
                ->first();

            if (!is_null($hotel_pos_doc)) {
                $hotel_orders = \App\Hotel\HotelOrderHeader::where('pos_doc_id', $hotel_pos_doc->id)
                    ->with(array('stay.room', 'stay.mainGuest.tercero'))
                    ->orderBy('id')
                    ->get();
            }
        }

        if (count($hotel_orders) == 0 && $hotel_sales_doc_id > 0 && !is_null($hotel_doc_for_pos_lookup) && \Illuminate\Support\Facades\Schema::hasTable('vtas_pos_doc_encabezados')) {
            $hotel_pos_doc_query = \App\VentasPos\FacturaPos::where('core_empresa_id', $hotel_doc_for_pos_lookup->core_empresa_id)
                ->where('consecutivo', $hotel_doc_for_pos_lookup->consecutivo);

            if (isset($hotel_doc_for_pos_lookup->valor_total)) {
                $hotel_pos_doc_query->where('valor_total', $hotel_doc_for_pos_lookup->valor_total);
            }

            $hotel_pos_doc = $hotel_pos_doc_query->orderBy('id', 'DESC')->first();

            if (!is_null($hotel_pos_doc)) {
                $hotel_orders = \App\Hotel\HotelOrderHeader::where('pos_doc_id', $hotel_pos_doc->id)
                    ->with(array('stay.room', 'stay.mainGuest.tercero'))
                    ->orderBy('id')
                    ->get();
            }
        }

        foreach ($hotel_orders as $hotel_order) {
            if (is_null($hotel_order->stay)) {
                continue;
            }

            $stay = $hotel_order->stay;
            $room_label = !is_null($stay->room) ? $stay->room->room_number : $stay->room_id;
            $guest_label = '';
            if (!is_null($stay->mainGuest) && !is_null($stay->mainGuest->tercero)) {
                $guest_label = $stay->mainGuest->tercero->descripcion;
            }

            $hotel_orders_invoice_details[] = array(
                'order_label' => $hotel_order->document_number != '' ? $hotel_order->document_number : 'HOT-' . $hotel_order->id,
                'room_label' => $room_label,
                'guest_label' => $guest_label,
                'check_in_at' => method_exists($stay, 'checkInAtDisplay') ? $stay->checkInAtDisplay() : $stay->check_in_at,
                'expected_check_out_at' => method_exists($stay, 'expectedCheckOutAtDisplay') ? $stay->expectedCheckOutAtDisplay() : $stay->expected_check_out_at,
                'check_out_at' => method_exists($stay, 'checkOutAtDisplay') ? $stay->checkOutAtDisplay() : $stay->check_out_at,
                'stay_days' => method_exists($stay, 'stayDays') ? $stay->stayDays() : '',
                'adults_count' => $stay->adults_count,
                'children_count' => $stay->children_count,
                'total_guests' => $stay->total_guests,
                'status' => $stay->status,
            );
        }
    }
?>

@if(count($hotel_orders_invoice_details) > 0)
    <div style="margin-top: 4px;">
        <b>Estadía Hotelera:</b>
        <?php $hotel_detail_index = 0; ?>
        @foreach($hotel_orders_invoice_details as $hotel_detail)
            <b>Pedido:</b> {{ $hotel_detail['order_label'] }}
            | <b>Habitación:</b> {{ $hotel_detail['room_label'] }}
            @if($hotel_detail['guest_label'] != '')
                | <b>Huésped:</b> {{ $hotel_detail['guest_label'] }}
            @endif
            | <b>Check-in:</b> {{ $hotel_detail['check_in_at'] }}
            | <b>Salida esperada:</b> {{ $hotel_detail['expected_check_out_at'] }}
            @if($hotel_detail['check_out_at'] != '')
                | <b>Check-out:</b> {{ $hotel_detail['check_out_at'] }}
            @endif
            | <b>Días:</b> {{ $hotel_detail['stay_days'] }}
            <?php $hotel_detail_index++; ?>
            @if($hotel_detail_index < count($hotel_orders_invoice_details))
                <br><br>
            @endif
        @endforeach
    </div>
@endif
