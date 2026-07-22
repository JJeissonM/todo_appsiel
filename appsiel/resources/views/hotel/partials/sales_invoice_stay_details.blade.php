<?php
    $hotel_orders_invoice_details = array();
    $hotel_module_enabled_value = strtolower((string)env('HOTEL_MODULE_ENABLED', false));
    $hotel_module_enabled = in_array($hotel_module_enabled_value, array('1', 'true', 'yes', 'on'));

    if (isset($doc_encabezado) && !is_null($doc_encabezado) && $hotel_module_enabled && \Illuminate\Support\Facades\Schema::hasTable('hotel_order_headers') && \Illuminate\Support\Facades\Schema::hasTable('hotel_stays') && \Illuminate\Support\Facades\Schema::hasTable('hotel_rooms')) {
        $hotel_order_query = \App\Hotel\HotelOrderHeader::where('sales_doc_id', $doc_encabezado->id);

        if (isset($doc_encabezado->ventas_doc_relacionado_id) && (int)$doc_encabezado->ventas_doc_relacionado_id > 0) {
            $hotel_order_query->orWhere('pos_doc_id', (int)$doc_encabezado->ventas_doc_relacionado_id);
        }

        $hotel_orders = $hotel_order_query->with(array('stay.room', 'stay.mainGuest.tercero'))
            ->orderBy('id')
            ->get();

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
            <b>Check-in:</b> {{ $hotel_detail['check_in_at'] }}
            @if($hotel_detail['check_out_at'] != '')
                | <b>Check-out:</b> {{ $hotel_detail['check_out_at'] }}
            @endif
            <b>Días:</b> {{ $hotel_detail['stay_days'] }}
            <?php $hotel_detail_index++; ?>
            @if($hotel_detail_index < count($hotel_orders_invoice_details))
                <br><br>
            @endif
        @endforeach
    </div>
@endif
