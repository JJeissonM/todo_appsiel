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
        $anticipos = $stay->anticiposCliente();
        $hotelService = new HotelService();
        $cancelBlockMessage = $hotelService->getCancelInvoiceBlockMessage($stay);
        $editBlockMessage = $hotelService->getEditDatesBlockMessage($stay);
        $checkOutBlockMessage = $hotelService->getCheckOutOpenOrdersBlockMessage($stay);
        $canCancelHotelOrder = $this->canCancelHotelOrder();
        $miga_pan = $this->breadcrumb('Estadia #' . $stay->id);
        return view('hotel.stays.show', compact('stay', 'clients', 'anticipos', 'cancelBlockMessage', 'editBlockMessage', 'checkOutBlockMessage', 'canCancelHotelOrder', 'miga_pan'));
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

    private function breadcrumb($label)
    {
        return HotelBreadcrumb::make('App\\Hotel\\HotelStay', $label);
    }
}
