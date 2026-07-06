<?php

namespace App\Hotel\Services;

use App\Contabilidad\Impuesto;
use App\Core\TipoDocApp;
use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelOrderLine;
use App\Hotel\HotelReservation;
use App\Hotel\HotelRoom;
use App\Hotel\HotelStay;
use App\Hotel\HotelStayGuest;
use App\Inventarios\InvProducto;
use App\Ventas\Cliente;
use App\Ventas\Services\PricesServices;
use App\Ventas\Vendedor;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use App\VentasPos\DocRegistro;
use App\VentasPos\FacturaPos;
use App\VentasPos\Pdv;
use App\VentasPos\Services\AccumulationService;
use App\VentasPos\Services\CxCService;
use App\VentasPos\Services\TreasuryService;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMotivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelService
{
    public function empresaId()
    {
        return Auth::user()->empresa_id;
    }

    public function userId()
    {
        return Auth::check() ? Auth::user()->id : null;
    }

    public function checkIn($data)
    {
        $service = $this;

        return DB::transaction(function () use ($data, $service) {
            $empresaId = $service->empresaId();
            $room = HotelRoom::where('empresa_id', $empresaId)->where('id', (int)$data['room_id'])->lockForUpdate()->first();
            $cliente = Cliente::find((int)$data['main_cliente_id']);
            if (is_null($cliente)) {
                throw new \Exception('El huesped principal no existe.');
            }

            $reservation = $service->reservationForCheckIn($room, $cliente->id, isset($data['check_in_at']) && $data['check_in_at'] != '' ? substr($data['check_in_at'], 0, 10) : date('Y-m-d'));
            if (is_null($room) || !$service->roomIsAvailableForCheckIn($room, $reservation)) {
                throw new \Exception('La habitacion no esta disponible para check-in.');
            }

            if ($service->hasActiveStay($empresaId, $room->id)) {
                throw new \Exception('La habitacion ya tiene una estadia activa.');
            }

            $adults = isset($data['adults_count']) ? (int)$data['adults_count'] : 1;
            $children = isset($data['children_count']) ? (int)$data['children_count'] : 0;

            $stay = HotelStay::create(array(
                'empresa_id' => $empresaId,
                'main_cliente_id' => $cliente->id,
                'room_id' => $room->id,
                'check_in_at' => isset($data['check_in_at']) && $data['check_in_at'] != '' ? $data['check_in_at'] : date('Y-m-d H:i:s'),
                'expected_check_out_at' => isset($data['expected_check_out_at']) && $data['expected_check_out_at'] != '' ? $data['expected_check_out_at'] : null,
                'adults_count' => $adults,
                'children_count' => $children,
                'total_guests' => max(1, $adults + $children),
                'status' => HotelStay::STATUS_ACTIVA,
                'notes' => isset($data['notes']) ? $data['notes'] : null,
                'created_by' => $service->userId(),
            ));

            HotelStayGuest::create(array(
                'empresa_id' => $empresaId,
                'stay_id' => $stay->id,
                'cliente_id' => $cliente->id,
                'is_main_guest' => 1,
            ));

            $room->status = HotelRoom::STATUS_OCUPADA;
            $room->save();

            $service->createOrderForStay($stay, true);

            if (!is_null($reservation)) {
                $reservation->fulfill($stay->id);
            }

            return $stay;
        });
    }

    public function createOrderForStay(HotelStay $stay, $includeRoomLine = false)
    {
        if ($stay->status != HotelStay::STATUS_ACTIVA) {
            throw new \Exception('Solo se pueden crear pedidos para una estadia activa.');
        }

        $room = $stay->room;
        $order = HotelOrderHeader::create(array(
            'empresa_id' => $stay->empresa_id,
            'stay_id' => $stay->id,
            'cliente_id' => $stay->main_cliente_id,
            'document_number' => $this->nextOrderNumber($stay),
            'order_date' => date('Y-m-d H:i:s'),
            'status' => HotelOrderHeader::STATUS_ABIERTO,
            'created_by' => $this->userId(),
        ));

        if ($includeRoomLine && !is_null($room) && !empty($room->inv_producto_id)) {
            $this->createLine($order, array(
                'producto_id' => $room->inv_producto_id,
                'room_id' => $room->id,
                'quantity' => 1,
                'source_type' => HotelOrderLine::SOURCE_ROOM,
                'source_id' => $room->id,
            ));
        }

        return $order;
    }

    public function checkOut(HotelStay $stay)
    {
        $service = $this;

        return DB::transaction(function () use ($stay, $service) {
            $stay = HotelStay::where('empresa_id', $service->empresaId())->where('id', $stay->id)->lockForUpdate()->first();
            if (is_null($stay) || $stay->status != HotelStay::STATUS_ACTIVA) {
                throw new \Exception('Solo se puede hacer check-out a estadias activas.');
            }

            $stay->check_out_at = date('Y-m-d H:i:s');
            $stay->status = HotelStay::STATUS_CERRADA;
            $stay->closed_by = $service->userId();
            $stay->save();

            $room = $stay->room;
            if (!is_null($room)) {
                $room->status = HotelRoom::STATUS_LIMPIEZA;
                $room->save();
            }

            return $stay;
        });
    }

    public function cancelStay(HotelStay $stay)
    {
        $service = $this;

        return DB::transaction(function () use ($stay, $service) {
            $stay = HotelStay::where('empresa_id', $service->empresaId())->where('id', $stay->id)->lockForUpdate()->first();
            if (is_null($stay) || $stay->status == HotelStay::STATUS_CERRADA) {
                throw new \Exception('No se puede anular una estadia cerrada.');
            }

            $stay->status = HotelStay::STATUS_ANULADA;
            $stay->save();

            if (!is_null($stay->room) && $stay->room->status == HotelRoom::STATUS_OCUPADA) {
                $stay->room->status = HotelRoom::STATUS_LIMPIEZA;
                $stay->room->save();
            }

            foreach ($stay->orders as $order) {
                if ($order->status == HotelOrderHeader::STATUS_ABIERTO) {
                    $order->status = HotelOrderHeader::STATUS_ANULADO;
                    $order->save();
                }
            }

            return $stay;
        });
    }

    public function createLine(HotelOrderHeader $order, $data)
    {
        if (!$order->canEditLines()) {
            throw new \Exception('El pedido no permite modificar lineas.');
        }

        $producto = InvProducto::find((int)$data['producto_id']);
        if (is_null($producto)) {
            throw new \Exception('El producto no existe.');
        }

        $quantity = isset($data['quantity']) ? (float)$data['quantity'] : 1;
        $unitPrice = isset($data['unit_price']) && $data['unit_price'] !== '' ? (float)$data['unit_price'] : $this->getProductPrice($producto->id, $order->cliente_id);
        $discount = isset($data['discount']) ? (float)$data['discount'] : 0;
        $taxData = $this->calculateTaxData($producto->id, $order->cliente_id, $quantity, $unitPrice, $discount);
        $taxValue = $taxData['valor_impuesto_total'];

        return HotelOrderLine::create(array(
            'empresa_id' => $order->empresa_id,
            'hotel_order_id' => $order->id,
            'producto_id' => $producto->id,
            'room_id' => isset($data['room_id']) && $data['room_id'] != '' ? (int)$data['room_id'] : null,
            'description' => isset($data['description']) && $data['description'] != '' ? $data['description'] : $producto->descripcion,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'discount' => $discount,
            'tax_value' => $taxValue,
            'line_total' => HotelOrderLine::calculateTotal($quantity, $unitPrice, $discount, $taxValue),
            'source_type' => isset($data['source_type']) ? $data['source_type'] : HotelOrderLine::SOURCE_MANUAL,
            'source_id' => isset($data['source_id']) && $data['source_id'] != '' ? (int)$data['source_id'] : null,
        ));
    }

    public function updateLine(HotelOrderHeader $order, HotelOrderLine $line, $data)
    {
        if (!$order->canEditLines()) {
            throw new \Exception('El pedido no permite modificar lineas.');
        }

        $quantity = isset($data['quantity']) ? (float)$data['quantity'] : $line->quantity;
        $unitPrice = isset($data['unit_price']) ? (float)$data['unit_price'] : $line->unit_price;
        $discount = isset($data['discount']) ? (float)$data['discount'] : $line->discount;
        $taxData = $this->calculateTaxData($line->producto_id, $order->cliente_id, $quantity, $unitPrice, $discount);
        $taxValue = $taxData['valor_impuesto_total'];

        $line->description = isset($data['description']) ? $data['description'] : $line->description;
        $line->quantity = $quantity;
        $line->unit_price = $unitPrice;
        $line->discount = $discount;
        $line->tax_value = $taxValue;
        $line->line_total = HotelOrderLine::calculateTotal($quantity, $unitPrice, $discount, $taxValue);
        $line->save();

        return $line;
    }

    public function deleteLine(HotelOrderHeader $order, HotelOrderLine $line)
    {
        if (!$order->canEditLines()) {
            throw new \Exception('El pedido no permite modificar lineas.');
        }

        $line->delete();
    }

    public function generateStandardInvoice(HotelOrderHeader $order)
    {
        $service = $this;

        return DB::transaction(function () use ($order, $service) {
            $order = $service->loadOpenOrder($order->id);
            $cliente = Cliente::find($order->cliente_id);
            if (is_null($cliente) || count($order->lines) == 0) {
                throw new \Exception('El pedido no tiene cliente valido o lineas para facturar.');
            }

            $tipoTransaccionId = (int)config('ventas.factura_ventas_tipo_transaccion_id', 23);
            $tipoDocAppId = (int)config('ventas.factura_ventas_tipo_doc_app_id', 18);
            $consecutivo = TipoDocApp::get_consecutivo_actual($order->empresa_id, $tipoDocAppId) + 1;
            TipoDocApp::aumentar_consecutivo($order->empresa_id, $tipoDocAppId);

            // Integracion minima: crea encabezado y registros en tablas de ventas.
            // La contabilizacion, cartera e inventario quedan para el flujo oficial si se requiere ampliar.
            $doc = VtasDocEncabezado::create(array(
                'core_empresa_id' => $order->empresa_id,
                'core_tipo_transaccion_id' => $tipoTransaccionId,
                'core_tipo_doc_app_id' => $tipoDocAppId,
                'consecutivo' => $consecutivo,
                'fecha' => date('Y-m-d'),
                'core_tercero_id' => $cliente->core_tercero_id,
                'cliente_id' => $cliente->id,
                'descripcion' => 'Factura generada desde pedido hotelero ' . $order->document_number,
                'estado' => 'Activo',
                'creado_por' => Auth::user()->email,
                'forma_pago' => $cliente->forma_pago(),
                'fecha_vencimiento' => $cliente->fecha_vencimiento_pago(date('Y-m-d')),
                'valor_total' => $order->lines->sum('line_total'),
            ));

            foreach ($order->lines as $line) {
                $service->createStandardInvoiceLine($doc->id, $line);
            }

            $order->status = HotelOrderHeader::STATUS_FACTURADO;
            $order->invoice_type = HotelOrderHeader::INVOICE_STANDARD;
            $order->sales_doc_id = $doc->id;
            $order->save();

            return $doc;
        });
    }

    public function generatePosInvoice(HotelOrderHeader $order, $lineasRegistrosMediosRecaudos = null, $formaPago = 'contado', $objectAnticipos = null)
    {
        $service = $this;

        return DB::transaction(function () use ($order, $service, $lineasRegistrosMediosRecaudos, $formaPago, $objectAnticipos) {
            $order = $service->loadOpenOrder($order->id);
            $cliente = Cliente::find($order->cliente_id);
            if (is_null($cliente) || count($order->lines) == 0) {
                throw new \Exception('El pedido no tiene cliente valido o lineas para facturar.');
            }

            $pdv = Pdv::find((int)config('ventas_pos.pdv_id_default', 1));
            if (is_null($pdv)) {
                throw new \Exception('No existe un punto de venta POS por defecto para generar y contabilizar la factura.');
            }

            $tipoDocAppId = (int)$pdv->tipo_doc_app_default_id;
            if ($tipoDocAppId <= 0) {
                throw new \Exception('El punto de venta POS por defecto no tiene tipo de documento configurado.');
            }

            $vendedorId = (int)$cliente->vendedor_id;
            if ($vendedorId <= 0) {
                $vendedorId = (int)config('ventas.vendedor_id');
            }
            if (is_null(Vendedor::find($vendedorId))) {
                throw new \Exception('No existe un vendedor valido para generar y contabilizar la factura POS.');
            }

            $consecutivo = TipoDocApp::get_consecutivo_actual($order->empresa_id, $tipoDocAppId) + 1;
            TipoDocApp::aumentar_consecutivo($order->empresa_id, $tipoDocAppId);
            $totalOrder = $order->lines->sum('line_total');
            $hasAnticipos = $service->hasAdvancePayments($objectAnticipos);
            $formaPago = $service->normalizePaymentType($formaPago);
            if ($hasAnticipos) {
                $objectAnticipos = $service->normalizeAdvancePayments($objectAnticipos);
                $formaPago = 'credito';
            }
            $lineasRegistrosMediosRecaudos = $service->normalizePaymentLines($lineasRegistrosMediosRecaudos, $totalOrder, $formaPago, $pdv, $hasAnticipos);

            $doc = FacturaPos::create(array(
                'uniqid' => uniqid(),
                'core_empresa_id' => $order->empresa_id,
                'core_tipo_transaccion_id' => 47,
                'core_tipo_doc_app_id' => $tipoDocAppId,
                'consecutivo' => $consecutivo,
                'fecha' => date('Y-m-d'),
                'core_tercero_id' => $cliente->core_tercero_id,
                'cliente_id' => $cliente->id,
                'vendedor_id' => $vendedorId,
                'pdv_id' => $pdv->id,
                'cajero_id' => Auth::check() ? Auth::user()->id : null,
                'forma_pago' => $formaPago,
                'fecha_vencimiento' => $cliente->fecha_vencimiento_pago(date('Y-m-d')),
                'lineas_registros_medios_recaudos' => $lineasRegistrosMediosRecaudos,
                'descripcion' => 'Factura POS generada desde pedido hotelero ' . $order->document_number,
                'valor_total' => $totalOrder,
                'valor_ajuste_al_peso' => 0,
                'valor_total_cambio' => 0,
                'valor_total_bolsas' => 0,
                'total_efectivo_recibido' => $service->paymentLinesTotal($lineasRegistrosMediosRecaudos),
                'estado' => 'Pendiente',
                'creado_por' => Auth::user()->email,
            ));

            foreach ($order->lines as $line) {
                $service->createPosInvoiceLine($doc->id, $line);
            }

            $accumulationService = new AccumulationService($pdv->id);
            $accumulationService->hacer_preparaciones_recetas('Creado por factura POS hotelera ' . $doc->get_label_documento(), $doc->fecha, $doc->id);
            $accumulationService->hacer_desarme_automatico('Creado por factura POS hotelera ' . $doc->get_label_documento(), $doc->fecha);
            $accumulationService->accumulate_one_invoice($doc->id);

            if ($hasAnticipos) {
                (new CxCService())->crear_cruce_con_anticipos($doc, $objectAnticipos);
                (new TreasuryService())->crear_abonos_documento($doc, $lineasRegistrosMediosRecaudos);
            }

            $order->status = HotelOrderHeader::STATUS_FACTURADO;
            $order->invoice_type = HotelOrderHeader::INVOICE_POS;
            $order->pos_doc_id = $doc->id;
            $order->save();

            return $doc;
        });
    }

    private function createStandardInvoiceLine($docId, HotelOrderLine $line)
    {
        VtasDocRegistro::create($this->invoiceLineData($line, array('vtas_doc_encabezado_id' => $docId)));
    }

    private function createPosInvoiceLine($docId, HotelOrderLine $line)
    {
        DocRegistro::create($this->invoiceLineData($line, array('vtas_pos_doc_encabezado_id' => $docId)));
    }

    private function invoiceLineData(HotelOrderLine $line, $header)
    {
        $producto = $line->product;
        $clienteId = !is_null($line->order) ? $line->order->cliente_id : 0;
        $taxData = $this->calculateTaxData($line->producto_id, $clienteId, $line->quantity, $line->unit_price, $line->discount);

        return $header + array(
            'vtas_motivo_id' => (int)config('ventas_pos.recetas_motivo_salida_id', 10),
            'inv_producto_id' => $line->producto_id,
            'impuesto_id' => !is_null($producto) ? $producto->impuesto_id : null,
            'precio_unitario' => $line->unit_price,
            'cantidad' => $line->quantity,
            'cantidad_pendiente' => $line->quantity,
            'cantidad_devuelta' => 0,
            'precio_total' => $line->line_total,
            'base_impuesto' => $taxData['base_impuesto_unitaria'],
            'tasa_impuesto' => $taxData['tasa_impuesto'],
            'valor_impuesto' => $taxData['valor_impuesto_unitario'],
            'base_impuesto_total' => $taxData['base_impuesto_total'],
            'tasa_descuento' => 0,
            'valor_total_descuento' => $line->discount,
            'creado_por' => Auth::user()->email,
            'estado' => 'Activo',
        );
    }

    private function calculateTaxData($productoId, $clienteId, $quantity, $unitPrice, $discount)
    {
        $quantity = (float)$quantity;
        $unitPrice = (float)$unitPrice;
        $discount = (float)$discount;
        $lineTotal = HotelOrderLine::calculateTotal($quantity, $unitPrice, $discount, 0);
        if ($lineTotal < 0) {
            $lineTotal = 0;
        }

        $tasa = $this->getTaxRate($productoId, $clienteId);
        $baseTotal = $lineTotal;
        $taxTotal = 0;

        if ($tasa > 0 && $lineTotal > 0) {
            // Appsiel maneja precios de venta con IVA incluido.
            $baseTotal = round($lineTotal / (1 + ($tasa / 100)), 2);
            $taxTotal = round($lineTotal - $baseTotal, 2);
        }

        $baseUnit = 0;
        $taxUnit = 0;
        if ($quantity > 0) {
            $baseUnit = round($baseTotal / $quantity, 6);
            $taxUnit = round($taxTotal / $quantity, 6);
        }

        return array(
            'tasa_impuesto' => $tasa,
            'base_impuesto_unitaria' => $baseUnit,
            'valor_impuesto_unitario' => $taxUnit,
            'base_impuesto_total' => $baseTotal,
            'valor_impuesto_total' => $taxTotal,
        );
    }

    private function getTaxRate($productoId, $clienteId)
    {
        return (float)Impuesto::get_tasa((int)$productoId, 0, (int)$clienteId);
    }

    private function getProductPrice($productoId, $clienteId)
    {
        $cliente = Cliente::find($clienteId);
        $listaPreciosId = !is_null($cliente) ? $cliente->lista_precios_id : (int)config('ventas.lista_precios_id', 1);

        $price = (new PricesServices())->get_item_price($listaPreciosId, date('Y-m-d'), $productoId, $clienteId);
        return is_null($price) ? 0 : $price;
    }

    private function roomIsAvailableForCheckIn($room, $reservation)
    {
        if (is_null($room)) {
            return false;
        }

        if ((int)$room->is_active != 1 || (int)$room->inv_producto_id <= 0) {
            return false;
        }

        if ($room->status == HotelRoom::STATUS_DISPONIBLE) {
            return true;
        }

        return $room->status == HotelRoom::STATUS_RESERVADA && !is_null($reservation);
    }

    private function hasActiveStay($empresaId, $roomId)
    {
        return HotelStay::where('empresa_id', $empresaId)->where('room_id', $roomId)->where('status', HotelStay::STATUS_ACTIVA)->count() > 0;
    }

    private function reservationForCheckIn($room, $clienteId, $date)
    {
        if (is_null($room)) {
            return null;
        }

        return HotelReservation::where('empresa_id', $room->empresa_id)
            ->where('room_id', $room->id)
            ->where('cliente_id', $clienteId)
            ->whereNotIn('status', array(HotelReservation::STATUS_ANULADA, HotelReservation::STATUS_CUMPLIDA))
            ->where('reserved_from', '<=', $date)
            ->where('reserved_until', '>=', $date)
            ->lockForUpdate()
            ->first();
    }

    private function loadOpenOrder($orderId)
    {
        $order = HotelOrderHeader::where('empresa_id', $this->empresaId())->where('id', $orderId)->with('lines.product.impuesto')->lockForUpdate()->first();
        if (is_null($order) || $order->status != HotelOrderHeader::STATUS_ABIERTO) {
            throw new \Exception('El pedido no esta abierto para facturar.');
        }

        return $order;
    }

    private function nextOrderNumber(HotelStay $stay)
    {
        $count = HotelOrderHeader::where('empresa_id', $stay->empresa_id)->where('stay_id', $stay->id)->count();
        if ($count == 0) {
            return 'HOT-' . $stay->id;
        }

        return 'HOT-' . $stay->id . '-' . ($count + 1);
    }

    private function normalizePaymentType($formaPago)
    {
        return $formaPago == 'credito' ? 'credito' : 'contado';
    }

    private function normalizePaymentLines($lineasRegistrosMediosRecaudos, $totalOrder, $formaPago, $pdv, $hasAnticipos = false)
    {
        if ($formaPago == 'credito' && !$hasAnticipos) {
            return '[]';
        }

        $lineas = json_decode((string)$lineasRegistrosMediosRecaudos, true);
        if (!is_array($lineas) || count($lineas) == 0) {
            throw new \Exception('Debe ingresar los medios de pago para facturar de contado o aplicar anticipos.');
        }

        $lineasValidas = array();
        foreach ($lineas as $linea) {
            if (!is_array($linea)) {
                continue;
            }

            $valor = isset($linea['valor']) ? $linea['valor'] : '';
            if ($this->parsePaymentValue($valor) <= 0) {
                continue;
            }

            $lineasValidas[] = array(
                'teso_medio_recaudo_id' => isset($linea['teso_medio_recaudo_id']) ? $linea['teso_medio_recaudo_id'] : '1-Efectivo',
                'teso_motivo_id' => isset($linea['teso_motivo_id']) ? $linea['teso_motivo_id'] : $this->defaultPaymentReason(),
                'teso_caja_id' => isset($linea['teso_caja_id']) ? $linea['teso_caja_id'] : $this->defaultCashBox($pdv),
                'teso_cuenta_bancaria_id' => isset($linea['teso_cuenta_bancaria_id']) ? $linea['teso_cuenta_bancaria_id'] : '0-',
                'valor' => '$' . $this->parsePaymentValue($valor),
            );
        }

        if (count($lineasValidas) == 0) {
            throw new \Exception('Debe ingresar medios de pago validos para facturar de contado.');
        }

        $totalPayments = 0;
        foreach ($lineasValidas as $lineaValida) {
            $totalPayments += $this->parsePaymentValue($lineaValida['valor']);
        }

        if (abs($totalPayments - (float)$totalOrder) > 1) {
            throw new \Exception('El valor total de los medios de pago debe ser igual al total del pedido hotelero.');
        }

        return json_encode($lineasValidas);
    }

    private function defaultPaymentReason()
    {
        $motivo = TesoMotivo::find((int)config('tesoreria.motivo_tesoreria_ventas_contado'));
        if (!is_null($motivo)) {
            return $motivo->id . '-' . $motivo->descripcion;
        }

        return '1-Recaudo clientes';
    }

    private function defaultCashBox($pdv)
    {
        if (!is_null($pdv) && (int)$pdv->caja_default_id > 0 && !is_null($pdv->caja)) {
            return $pdv->caja_default_id . '-' . $pdv->caja->descripcion;
        }

        $caja = TesoCaja::find(1);
        if (!is_null($caja)) {
            return $caja->id . '-' . $caja->descripcion;
        }

        return '1-Caja general';
    }

    private function paymentLinesTotal($lineasRegistrosMediosRecaudos)
    {
        $lineas = json_decode((string)$lineasRegistrosMediosRecaudos, true);
        if (!is_array($lineas)) {
            return 0;
        }

        $total = 0;
        foreach ($lineas as $linea) {
            $medioRecaudo = isset($linea['teso_medio_recaudo_id']) ? $linea['teso_medio_recaudo_id'] : '';
            $medioRecaudoParts = explode('-', $medioRecaudo);
            if ((int)$medioRecaudoParts[0] == 0) {
                continue;
            }

            $total += $this->parsePaymentValue(isset($linea['valor']) ? $linea['valor'] : 0);
        }

        return $total;
    }

    private function parsePaymentValue($value)
    {
        $value = trim((string)$value);
        $value = str_replace('$', '', $value);
        $value = str_replace(' ', '', $value);

        if (strpos($value, ',') !== false && strpos($value, '.') !== false) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        } else {
            $value = str_replace(',', '', $value);
        }

        return (float)$value;
    }

    private function hasAdvancePayments($objectAnticipos)
    {
        $objectAnticipos = trim((string)$objectAnticipos);
        return $objectAnticipos != '' && $objectAnticipos != 'null' && $objectAnticipos != '[]';
    }

    private function normalizeAdvancePayments($objectAnticipos)
    {
        $decoded = json_decode('[' . (string)$objectAnticipos . ']', true);
        if (!is_array($decoded) || count($decoded) == 0) {
            throw new \Exception('Los anticipos aplicados no tienen un formato valido.');
        }

        $normalized = array();
        foreach ($decoded as $advance) {
            if (!is_array($advance)) {
                continue;
            }

            $cxcMovimientoId = isset($advance['cxc_movimiento_id']) ? (int)$advance['cxc_movimiento_id'] : 0;
            $valorAplicar = isset($advance['valor_aplicar']) ? abs($this->parsePaymentValue($advance['valor_aplicar'])) : 0;

            if ($cxcMovimientoId <= 0 || $valorAplicar <= 0) {
                continue;
            }

            $normalized[] = array(
                'cxc_movimiento_id' => (string)$cxcMovimientoId,
                'Documento' => isset($advance['Documento']) ? $advance['Documento'] : '',
                'Fecha' => isset($advance['Fecha']) ? $advance['Fecha'] : '',
                'saldo_pendiente' => isset($advance['saldo_pendiente']) ? $advance['saldo_pendiente'] : '',
                'valor_aplicar' => (string)($valorAplicar * -1),
            );
        }

        if (count($normalized) == 0) {
            throw new \Exception('No hay anticipos validos para aplicar.');
        }

        $parts = array();
        foreach ($normalized as $advance) {
            $parts[] = json_encode($advance);
        }

        return implode(',', $parts);
    }
}
