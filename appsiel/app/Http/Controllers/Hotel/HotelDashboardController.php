<?php

namespace App\Http\Controllers\Hotel;

use App\CxC\CxcMovimiento;
use App\Hotel\HotelReservation;
use App\Hotel\HotelRoom;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Sistema\Modelo;
use App\Sistema\Services\ModeloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HotelDashboardController extends Controller
{
    public function __construct()
    {
        HotelBreadcrumb::ensureContext('App\\Hotel\\HotelRoom');
    }

    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $this->syncTodayReservations($empresaId);

        $query = HotelRoom::where('empresa_id', $empresaId)
            ->with('product', 'activeStay.orders', 'activeStay.mainGuest.tercero', 'activeTodayReservation.cliente.tercero')
            ->orderBy('floor')
            ->orderBy('room_number');

        if ($request->floor != '') {
            $query->where('floor', $request->floor);
        }

        if ($request->status != '') {
            $query->where('status', $request->status);
        }

        $rooms = $query->get();

        $floors = HotelRoom::where('empresa_id', $empresaId)
            ->whereNotNull('floor')
            ->where('floor', '<>', '')
            ->groupBy('floor')
            ->orderBy('floor')
            ->lists('floor', 'floor')
            ->toArray();

        $statuses = HotelRoom::options(HotelRoom::statuses());
        $summary = $this->summary($empresaId);
        $activeReservations = $this->activeReservations($empresaId);
        $customerAdvances = $this->customerAdvances($empresaId);
        $miga_pan = HotelBreadcrumb::dashboard('Habitaciones');
        $appId = HotelBreadcrumb::appId();
        $roomModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelRoom');
        $stayModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelStay');
        $orderModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelOrderHeader');
        $reservationModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelReservation');
        $guestModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelGuest');
        if ($guestModelId == 0) {
            $guestModelId = 138;
        }
        $roomIndexUrl = HotelBreadcrumb::crudIndexUrl('App\\Hotel\\HotelRoom');
        $roomCreateUrl = HotelBreadcrumb::crudCreateUrl('App\\Hotel\\HotelRoom');
        $guestCreateUrl = $guestModelId == 138 ? HotelBreadcrumb::crudCreateUrl('App\\Ventas\\Cliente') : HotelBreadcrumb::crudCreateUrl('App\\Hotel\\HotelGuest');
        $guestFormCreate = $this->guestFormCreate($guestModelId);

        return view('hotel.index', compact('rooms', 'floors', 'statuses', 'summary', 'activeReservations', 'customerAdvances', 'miga_pan', 'appId', 'roomModelId', 'stayModelId', 'orderModelId', 'reservationModelId', 'guestModelId', 'roomIndexUrl', 'roomCreateUrl','guestCreateUrl', 'guestFormCreate'));
    }

    private function syncTodayReservations($empresaId)
    {
        $today = date('Y-m-d');

        $reservedRoomIds = HotelReservation::where('empresa_id', $empresaId)
            ->whereNotIn('status', array(HotelReservation::STATUS_ANULADA, HotelReservation::STATUS_CUMPLIDA))
            ->where('reserved_from', '<=', $today)
            ->where('reserved_until', '>=', $today)
            ->lists('room_id')
            ->toArray();

        if (count($reservedRoomIds) > 0) {
            HotelRoom::where('empresa_id', $empresaId)
                ->whereIn('id', $reservedRoomIds)
                ->where('status', HotelRoom::STATUS_DISPONIBLE)
                ->update(array('status' => HotelRoom::STATUS_RESERVADA));
        }

        HotelRoom::where('empresa_id', $empresaId)
            ->where('status', HotelRoom::STATUS_RESERVADA)
            ->whereNotIn('id', count($reservedRoomIds) > 0 ? $reservedRoomIds : array(0))
            ->update(array('status' => HotelRoom::STATUS_DISPONIBLE));
    }

    private function summary($empresaId)
    {
        $summary = array();
        foreach (HotelRoom::statuses() as $status) {
            $summary[$status] = HotelRoom::where('empresa_id', $empresaId)->where('status', $status)->count();
        }

        return $summary;
    }

    private function activeReservations($empresaId)
    {
        return HotelReservation::where('empresa_id', $empresaId)
            ->whereNotIn('status', array(HotelReservation::STATUS_ANULADA, HotelReservation::STATUS_CUMPLIDA))
            ->with('room', 'cliente.tercero')
            ->orderBy('reserved_from')
            ->get();
    }

    private function customerAdvances($empresaId)
    {
        return CxcMovimiento::leftJoin('core_terceros', 'core_terceros.id', '=', 'cxc_movimientos.core_tercero_id')
            ->leftJoin('core_tipos_docs_apps', 'core_tipos_docs_apps.id', '=', 'cxc_movimientos.core_tipo_doc_app_id')
            ->where('cxc_movimientos.core_empresa_id', $empresaId)
            ->where('cxc_movimientos.saldo_pendiente', '<', -0.1)
            ->select(
                'cxc_movimientos.id',
                'core_terceros.descripcion AS tercero',
                DB::raw('CONCAT(core_tipos_docs_apps.prefijo, " ", cxc_movimientos.consecutivo) AS documento'),
                'cxc_movimientos.fecha',
                'cxc_movimientos.detalle',
                'cxc_movimientos.saldo_pendiente'
            )
            ->orderBy('cxc_movimientos.fecha', 'DESC')
            ->get();
    }

    private function guestFormCreate($guestModelId)
    {
        $modelo = Modelo::find($guestModelId);
        if (is_null($modelo)) {
            return array('url' => 'vtas_clientes', 'campos' => array());
        }

        $listaCampos = ModeloController::get_campos_modelo($modelo, '', 'create');

        if (method_exists(app($modelo->name_space), 'get_campos_adicionales_create')) {
            $listaCampos = app($modelo->name_space)->get_campos_adicionales_create($listaCampos);
        }

        $acciones = (new ModeloService())->acciones_basicas_modelo($modelo, '');

        return array(
            'url' => $acciones->store,
            'campos' => $listaCampos,
        );
    }
}
