<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelRoom;
use App\Hotel\HotelStay;
use App\Hotel\Services\HotelReceivableService;
use App\Hotel\Services\HotelService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Hotel\Support\HotelCreatorLabel;
use App\Http\Controllers\Controller;
use App\CxC\CxcMovimiento;
use App\Ventas\Cliente;
use App\VentasPos\FacturaPos;
use App\VentasPos\Services\FacturaPosService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelStayController extends Controller
{
    public function __construct()
    {
        HotelBreadcrumb::ensureContext('App\\Hotel\\HotelStay');
    }

    public function index()
    {
        $stays = HotelStay::where('empresa_id', Auth::user()->empresa_id)->with('room', 'mainGuest.tercero')->orderBy('check_in_at', 'DESC')->paginate(20);
        $stays->appends(request()->except('page'));
        $miga_pan = $this->breadcrumb('Estadias');
        return view('hotel.stays.index', compact('stays', 'miga_pan'));
    }

    public function active()
    {
        $stays = HotelStay::where('empresa_id', Auth::user()->empresa_id)->where('status', HotelStay::STATUS_ACTIVA)->with('room', 'mainGuest.tercero')->orderBy('check_in_at', 'DESC')->paginate(20);
        $stays->appends(request()->except('page'));
        $miga_pan = $this->breadcrumb('Estadias activas');
        return view('hotel.stays.active', compact('stays', 'miga_pan'));
    }

    public function show($id)
    {
        $stay = $this->findStay($id);
        $stay->ensureCheckInRecords();
        $stay = $this->findStay($id);
        $clients = $this->clientsList();
        $anticipos = $stay->anticiposCliente();
        $facturasCredito = (new HotelReceivableService())->pendingInvoices($stay);
        $facturasPosIndependientes = $this->independentPosInvoices($stay);
        $saldoPedidos = $this->openOrdersBalance($stay);
        $saldoFacturasCredito = $this->receivablesBalance($facturasCredito);
        $saldoAnticipos = abs(min(0, $this->receivablesBalance($anticipos)));
        $saldoPendienteNeto = max(0, $saldoPedidos + $saldoFacturasCredito - $saldoAnticipos);
        $hotelService = new HotelService();
        $cancelBlockMessage = $hotelService->getCancelInvoiceBlockMessage($stay);
        $editBlockMessage = $hotelService->getEditDatesBlockMessage($stay);
        $checkOutBlockMessage = $hotelService->getCheckOutBlockMessage($stay);
        $canCancelHotelOrder = $this->canCancelHotelOrder();
        $canCancelIndependentPosInvoice = $this->canCancelIndependentPosInvoice();
        $miga_pan = $this->breadcrumb('Estadia #' . $stay->id);
        return view('hotel.stays.show', compact('stay', 'clients', 'anticipos', 'facturasCredito', 'facturasPosIndependientes', 'saldoPedidos', 'saldoFacturasCredito', 'saldoAnticipos', 'saldoPendienteNeto', 'cancelBlockMessage', 'editBlockMessage', 'checkOutBlockMessage', 'canCancelHotelOrder', 'canCancelIndependentPosInvoice', 'miga_pan'));
    }

    public function createCheckIn()
    {
        $rooms = HotelRoom::where('empresa_id', Auth::user()->empresa_id)
            ->whereIn('status', array(HotelRoom::STATUS_DISPONIBLE, HotelRoom::STATUS_RESERVADA))
            ->where('is_active', 1)
            ->orderBy('room_number')
            ->get();
        $clients = $this->clientsList();
        $miga_pan = $this->breadcrumb('Check-in');

        return view('hotel.stays.check_in', compact('rooms', 'clients', 'miga_pan'));
    }

    public function storeCheckIn(Request $request)
    {
        $this->validate($request, array(
            'main_cliente_id' => 'required|exists:vtas_clientes,id',
            'room_id' => 'required|exists:hotel_rooms,id',
            'expected_check_out_at' => 'required',
            'adults_count' => 'required|integer|min:1',
            'children_count' => 'integer|min:0',
        ), array(
            'expected_check_out_at.required' => 'Debe ingresar la salida esperada.',
        ));

        try {
            $stay = (new HotelService())->checkIn($request->all());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', 'Check-in registrado correctamente.');
    }

    public function checkOut(Request $request, $id)
    {
        $stay = $this->findStay($id);

        $this->validate($request, array(
            'check_out_at' => 'required',
        ), array(
            'check_out_at.required' => 'Debe ingresar la fecha y hora de check-out.',
        ));

        try {
            $stay = (new HotelService())->checkOut($stay, $request->check_out_at);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', 'Check-out registrado correctamente.');
    }

    public function createOrder($id)
    {
        $stay = $this->findStay($id);

        try {
            $order = (new HotelService())->createOrderForStay($stay, false);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/orders/' . $order->id, array('id_modelo' => HotelBreadcrumb::modelId('App\\Hotel\\HotelOrderHeader'))))->with('flash_message', 'Pedido hotelero creado correctamente.');
    }

    public function cancel($id)
    {
        $stay = $this->findStay($id);

        try {
            (new HotelService())->cancelStay($stay);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', 'Estadia anulada correctamente.');
    }

    public function cancelIndependentPosInvoice($stayId, $invoiceId)
    {
        if (!$this->canCancelIndependentPosInvoice()) {
            return redirect()->back()->with('mensaje_error', 'Solo un usuario administrador puede anular facturas desde la estadía.');
        }

        $stay = $this->findStay($stayId);
        $invoice = $this->independentPosInvoices($stay)->first(function ($key, $row) use ($invoiceId) {
            return (int)$row->id === (int)$invoiceId;
        });

        if (is_null($invoice)) {
            return redirect()->back()->with('mensaje_error', 'La factura no pertenece a esta estadía o está asociada a un pedido hotelero.');
        }

        if (strtolower(trim((string)$invoice->estado)) == 'anulado') {
            return redirect()->back()->with('mensaje_error', 'La factura ya se encuentra anulada.');
        }

        $service = new FacturaPosService();
        $paymentValidation = $service->factura_tiene_abonos_cxc($invoice->id);
        if ($paymentValidation->status) {
            return redirect()->back()->with('mensaje_error', $paymentValidation->message);
        }

        try {
            DB::transaction(function () use ($service, $invoice, $stay) {
                $lockedInvoice = FacturaPos::where('id', $invoice->id)
                    ->where('core_empresa_id', $stay->empresa_id)
                    ->lockForUpdate()
                    ->first();

                if (is_null($lockedInvoice) || strtolower(trim((string)$lockedInvoice->estado)) == 'anulado') {
                    throw new \Exception('La factura ya no está disponible para anulación.');
                }

                $service->anular_factura_contabilizada((int)$lockedInvoice->id, true);
            });
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))
            ->with('flash_message', 'Factura POS anulada correctamente desde la estadía.');
    }

    private function findStay($id)
    {
        return HotelStay::where('empresa_id', Auth::user()->empresa_id)->where('id', $id)->with('room', 'mainGuest.tercero', 'guests.cliente.tercero', 'orders.lines.product', 'orders.posInvoice.tipo_documento_app', 'orders.salesInvoice.tipo_documento_app')->firstOrFail();
    }

    private function clientsList()
    {
        $rows = Cliente::leftJoin('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')
            ->where('vtas_clientes.estado', 'Activo')
            ->select('vtas_clientes.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->orderBy('core_terceros.descripcion')
            ->get();

        $options = array('' => '');
        foreach ($rows as $row) {
            $options[$row->id] = $row->numero_identificacion . ' - ' . $row->descripcion;
        }
        return $options;
    }

    private function canCancelHotelOrder()
    {
        if (!Auth::check()) {
            return false;
        }

        $user = Auth::user();

        if (method_exists($user, 'can')) {
            try {
                if ($user->can('hotel_pedido_anular')) {
                    return true;
                }
            } catch (\Exception $e) {
                // Algunas instalaciones antiguas pueden no tener este permiso sembrado.
            }
        }

        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasRole('Admin Colegio')) {
                return true;
            }
        }

        return false;
    }

    private function canCancelIndependentPosInvoice()
    {
        if (!Auth::check() || !method_exists(Auth::user(), 'hasRole')) {
            return false;
        }

        $user = Auth::user();
        return $user->hasRole('SuperAdmin') || $user->hasRole('Administrador') || $user->hasRole('Admin Colegio');
    }

    private function openOrdersBalance(HotelStay $stay)
    {
        $total = 0;
        foreach ($stay->orders as $order) {
            if ($order->status != HotelOrderHeader::STATUS_ABIERTO) {
                continue;
            }

            foreach ($order->lines as $line) {
                $total += (float)$line->line_total;
            }
        }

        return $total;
    }

    private function receivablesBalance($rows)
    {
        $total = 0;
        foreach ($rows as $row) {
            $total += isset($row['saldo_pendiente']) ? (float)$row['saldo_pendiente'] : 0;
        }

        return $total;
    }

    private function independentPosInvoices(HotelStay $stay)
    {
        $clientIds = array((int)$stay->main_cliente_id);
        $terceroIds = array();

        if (!is_null($stay->mainGuest) && !empty($stay->mainGuest->core_tercero_id)) {
            $terceroIds[] = (int)$stay->mainGuest->core_tercero_id;
        }

        foreach ($stay->guests as $guest) {
            if (!empty($guest->cliente_id)) {
                $clientIds[] = (int)$guest->cliente_id;
            }

            if (!is_null($guest->cliente) && !empty($guest->cliente->core_tercero_id)) {
                $terceroIds[] = (int)$guest->cliente->core_tercero_id;
            }
        }

        $clientIds = array_values(array_unique(array_filter($clientIds)));
        $terceroIds = array_values(array_unique(array_filter($terceroIds)));
        $associatedPosInvoiceIds = HotelOrderHeader::where('empresa_id', $stay->empresa_id)
            ->whereNotNull('pos_doc_id')
            ->where('pos_doc_id', '>', 0)
            ->lists('pos_doc_id')
            ->toArray();

        $dateFrom = substr((string)$stay->check_in_at, 0, 10);
        $dateTo = !empty($stay->check_out_at) ? substr((string)$stay->check_out_at, 0, 10) : date('Y-m-d');

        $query = FacturaPos::where('core_empresa_id', $stay->empresa_id)
            ->where('fecha', '>=', $dateFrom)
            ->where('fecha', '<=', $dateTo)
            ->where(function ($query) use ($clientIds, $terceroIds) {
                if (count($clientIds) > 0) {
                    $query->whereIn('cliente_id', $clientIds);
                }

                if (count($terceroIds) > 0) {
                    $method = count($clientIds) > 0 ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('core_tercero_id', $terceroIds);
                }
            })
            ->with('tipo_documento_app', 'cliente.tercero')
            ->orderBy('fecha', 'DESC')
            ->orderBy('id', 'DESC');

        if (count($associatedPosInvoiceIds) > 0) {
            $query->whereNotIn('id', $associatedPosInvoiceIds);
        }

        $invoices = $query->get();
        $movementsByDocument = $this->receivableMovementsByPosInvoice($invoices, $stay->empresa_id);
        foreach ($invoices as $invoice) {
            $invoice->hotel_creator_label = HotelCreatorLabel::userLabel(
                $invoice->creado_por,
                !empty($invoice->created_at) ? $invoice->created_at : $invoice->fecha,
                $invoice->pdv_id
            );
            $invoice->hotel_total = (float)$invoice->valor_total + (float)$invoice->valor_ajuste_al_peso + (float)$invoice->valor_total_bolsas;

            $movementKey = $this->posInvoiceMovementKey($invoice);
            $movement = isset($movementsByDocument[$movementKey]) ? $movementsByDocument[$movementKey] : null;
            $this->setIndependentPosInvoiceStatus($invoice, $movement);
        }

        return $invoices;
    }

    private function receivableMovementsByPosInvoice($invoices, $companyId)
    {
        if ($invoices->count() == 0) {
            return array();
        }

        $movements = CxcMovimiento::where('core_empresa_id', $companyId)
            ->whereIn('core_tipo_transaccion_id', $invoices->pluck('core_tipo_transaccion_id')->unique()->values()->all())
            ->whereIn('core_tipo_doc_app_id', $invoices->pluck('core_tipo_doc_app_id')->unique()->values()->all())
            ->whereIn('consecutivo', $invoices->pluck('consecutivo')->unique()->values()->all())
            ->whereIn('core_tercero_id', $invoices->pluck('core_tercero_id')->filter()->unique()->values()->all())
            ->orderBy('id', 'DESC')
            ->get();

        $indexed = array();
        foreach ($movements as $movement) {
            $key = $this->posInvoiceMovementKey($movement);
            if (!isset($indexed[$key])) {
                $indexed[$key] = $movement;
            }
        }

        return $indexed;
    }

    private function posInvoiceMovementKey($document)
    {
        return implode('|', array(
            (int)$document->core_tipo_transaccion_id,
            (int)$document->core_tipo_doc_app_id,
            (int)$document->consecutivo,
            (int)$document->core_tercero_id,
        ));
    }

    private function setIndependentPosInvoiceStatus(FacturaPos $invoice, $movement)
    {
        $invoiceState = strtolower(trim((string)$invoice->estado));

        if ($invoiceState == 'anulado') {
            $invoice->hotel_status_label = 'ANULADO';
            $invoice->hotel_status_class = 'label-danger';
            return;
        }

        if (!is_null($movement)) {
            if ((float)$movement->saldo_pendiente > 0.1) {
                $invoice->hotel_status_label = 'PENDIENTE POR PAGAR';
                $invoice->hotel_status_class = 'label-warning';
                return;
            }

            $invoice->hotel_status_label = 'PAGADO';
            $invoice->hotel_status_class = 'label-success';
            return;
        }

        if (strtolower(trim((string)$invoice->forma_pago)) == 'contado') {
            $invoice->hotel_status_label = 'PAGADO';
            $invoice->hotel_status_class = 'label-success';
            return;
        }

        $invoice->hotel_status_label = 'FACTURADO';
        $invoice->hotel_status_class = 'label-info';
    }

    private function breadcrumb($label)
    {
        return HotelBreadcrumb::make('App\\Hotel\\HotelStay', $label);
    }
}
