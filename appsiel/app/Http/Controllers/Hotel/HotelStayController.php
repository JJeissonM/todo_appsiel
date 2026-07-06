<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelOrderHeader;
use App\Hotel\HotelRoom;
use App\Hotel\HotelStay;
use App\Hotel\Services\HotelService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\CxC\CxcMovimiento;
use App\Ventas\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $anticipos = $this->anticiposCliente($stay);
        $cancelBlockMessage = (new HotelService())->getCancelInvoiceBlockMessage($stay);
        $miga_pan = $this->breadcrumb('Estadia #' . $stay->id);
        return view('hotel.stays.show', compact('stay', 'clients', 'anticipos', 'cancelBlockMessage', 'miga_pan'));
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
            'adults_count' => 'required|integer|min:1',
            'children_count' => 'integer|min:0',
        ));

        try {
            $stay = (new HotelService())->checkIn($request->all());
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('mensaje_error', $e->getMessage());
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', 'Check-in registrado correctamente.');
    }

    public function checkOut($id)
    {
        $stay = $this->findStay($id);

        try {
            $stay = (new HotelService())->checkOut($stay);
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        $message = 'Check-out registrado correctamente.';
        $openOrders = HotelOrderHeader::where('empresa_id', Auth::user()->empresa_id)
            ->where('stay_id', $stay->id)
            ->where('status', HotelOrderHeader::STATUS_ABIERTO)
            ->count();
        if ($openOrders > 0) {
            return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('mensaje_error', 'La estadia fue cerrada, pero el pedido hotelero aun no ha sido facturado.');
        }

        return redirect(HotelBreadcrumb::url('hotel/stays/' . $stay->id))->with('flash_message', $message);
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

    private function anticiposCliente(HotelStay $stay)
    {
        if (is_null($stay->mainGuest) || empty($stay->mainGuest->core_tercero_id)) {
            return array();
        }

        $rows = CxcMovimiento::get_documentos_tercero($stay->mainGuest->core_tercero_id, date('Y-m-d'));
        $anticipos = array();
        foreach ($rows as $row) {
            if ((float)$row['saldo_pendiente'] < -0.1) {
                $anticipos[] = $row;
            }
        }

        return $anticipos;
    }

    private function breadcrumb($label)
    {
        return HotelBreadcrumb::make('App\\Hotel\\HotelStay', $label);
    }
}
