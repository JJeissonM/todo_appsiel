<?php
    $hotel_orders_invoice_details = array();
    $hotel_module_enabled_value = strtolower((string)env('HOTEL_MODULE_ENABLED', false));
    $hotel_module_enabled = in_array($hotel_module_enabled_value, array('1', 'true', 'yes', 'on'));
    $hotel_pos_doc_id = 0;

    if (isset($doc_encabezado) && !is_null($doc_encabezado) && isset($doc_encabezado->id)) {
        $hotel_pos_doc_id = (int)$doc_encabezado->id;
    }

    if ($hotel_pos_doc_id <= 0 && isset($datos_factura) && !is_null($datos_factura) && isset($datos_factura->id)) {
        $hotel_pos_doc_id = (int)$datos_factura->id;
    }

    if ($hotel_pos_doc_id <= 0 && isset($id)) {
        $hotel_pos_doc_id = (int)$id;
    }

    if ($hotel_pos_doc_id > 0 && $hotel_module_enabled && \Illuminate\Support\Facades\Schema::hasTable('hotel_order_headers') && \Illuminate\Support\Facades\Schema::hasTable('hotel_stays') && \Illuminate\Support\Facades\Schema::hasTable('hotel_rooms')) {
        $hotel_orders = \App\Hotel\HotelOrderHeader::where('pos_doc_id', $hotel_pos_doc_id)
            ->with('stay.room')
            ->orderBy('id')
            ->get();

        foreach ($hotel_orders as $hotel_order) {
            if (is_null($hotel_order->stay)) {
                continue;
            }

            $stay = $hotel_order->stay;
            $room_label = !is_null($stay->room) ? $stay->room->room_number : $stay->room_id;
            $hotel_orders_invoice_details[] = array(
                'order_label' => $hotel_order->document_number != '' ? $hotel_order->document_number : 'HOT-' . $hotel_order->id,
                'room_label' => $room_label,
                'check_in_at' => $stay->check_in_at,
                'expected_check_out_at' => $stay->expected_check_out_at,
            );
        }
    }
?>

@if(count($hotel_orders_invoice_details) > 0)
    <br>
    <b>Estadia hotelera: &nbsp;&nbsp;</b>
    <?php $hotel_detail_index = 0; ?>
    @foreach($hotel_orders_invoice_details as $hotel_detail)
        @if($hotel_detail_index > 0)
            <br>
            <span style="display: inline-block; width: 120px;">&nbsp;</span>
        @endif
        Pedido {{ $hotel_detail['order_label'] }}
        | Habitacion {{ $hotel_detail['room_label'] }}
        | Check-in: {{ $hotel_detail['check_in_at'] }}
        | Salida esperada: {{ $hotel_detail['expected_check_out_at'] }}
        <?php $hotel_detail_index++; ?>
    @endforeach
@endif
