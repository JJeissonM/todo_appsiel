<?php

namespace App\Http\Controllers\Hotel;

use App\CxC\CxcMovimiento;
use App\Hotel\HotelReservation;
use App\Hotel\HotelRoom;
use App\Hotel\Services\HotelService;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Sistema\Modelo;
use App\Sistema\Services\ModeloService;
use App\VentasPos\AperturaEncabezado;
use App\VentasPos\CierreEncabezado;
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
        $pdvData = $this->pdvData($request);
        $dashboardEnabled = !empty($pdvData['can_view_dashboard']);
        $dashboardCanTransact = !empty($pdvData['can_transact']);

        if ($dashboardEnabled) {
            $this->syncTodayReservations($empresaId);
        }

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

        $rooms = $dashboardEnabled ? $query->get() : collect();

        $floors = $dashboardEnabled ? HotelRoom::where('empresa_id', $empresaId)
            ->whereNotNull('floor')
            ->where('floor', '<>', '')
            ->groupBy('floor')
            ->orderBy('floor')
            ->lists('floor', 'floor')
            ->toArray() : array();

        $statuses = HotelRoom::options(HotelRoom::statuses());
        $summary = $dashboardEnabled ? $this->summary($empresaId) : array();
        $activeReservations = $dashboardEnabled ? $this->activeReservations($empresaId) : collect();
        $customerAdvances = $dashboardEnabled ? $this->customerAdvances($empresaId) : collect();
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

        return view('hotel.index', compact('rooms', 'floors', 'statuses', 'summary', 'activeReservations', 'customerAdvances', 'miga_pan', 'appId', 'roomModelId', 'stayModelId', 'orderModelId', 'reservationModelId', 'guestModelId', 'roomIndexUrl', 'roomCreateUrl','guestCreateUrl', 'guestFormCreate', 'pdvData', 'dashboardEnabled', 'dashboardCanTransact'));
    }

    private function pdvData(Request $request)
    {
        $hotelService = new HotelService();
        $pdv = $hotelService->currentCashierPdv();
        $returnTo = $request->fullUrl();
        $isAdmin = $hotelService->userCanViewDashboardWithoutPdv();

        if (is_null($pdv)) {
            return array(
                'pdv' => null,
                'status' => $isAdmin ? 'Modo administrador' : 'Sin PDV',
                'color' => $isAdmin ? '#3c8dbc' : '#d9534f',
                'since' => '--',
                'message' => $isAdmin ? 'Usuario administrador: puede consultar el dashboard sin punto de venta asociado. Las transacciones POS requieren un PDV abierto.' : 'El usuario actual no tiene un punto de venta POS asociado.',
                'can_view_dashboard' => $isAdmin,
                'can_transact' => false,
                'is_admin' => $isAdmin,
                'apertura_url' => '',
                'cierre_url' => '',
                'arqueo_url' => '',
                'factura_directa_url' => '',
            );
        }

        $apertura = AperturaEncabezado::where('pdv_id', $pdv->id)->orderBy('created_at', 'DESC')->first();
        $cierre = CierreEncabezado::where('pdv_id', $pdv->id)->orderBy('created_at', 'DESC')->first();
        $status = $pdv->estado;
        if (!in_array($status, array('Abierto', 'Cerrado', 'Inactivo'))) {
            if (!is_null($apertura) && (is_null($cierre) || $apertura->created_at > $cierre->created_at)) {
                $status = 'Abierto';
            } else {
                $status = 'Cerrado';
            }
        }

        $since = '--';
        if ($status == 'Abierto' && !is_null($apertura)) {
            $since = $apertura->fecha;
        }
        if ($status == 'Cerrado' && !is_null($cierre)) {
            $since = $cierre->created_at;
        }

        $returnParam = '&return_to=' . urlencode($returnTo);

        return array(
            'pdv' => $pdv,
            'status' => $status,
            'color' => $status == 'Abierto' ? '#00a65a' : '#dd4b39',
            'since' => $since,
            'message' => '',
            'can_view_dashboard' => $isAdmin || $status == 'Abierto',
            'can_transact' => $status == 'Abierto',
            'is_admin' => $isAdmin,
            'apertura_url' => 'web/create?id=20&id_modelo=228&id_transaccion=45&pdv_id=' . $pdv->id . '&cajero_id=' . Auth::user()->id . $returnParam,
            'cierre_url' => 'web/create?id=20&id_modelo=229&id_transaccion=46&pdv_id=' . $pdv->id . '&cajero_id=' . Auth::user()->id . $returnParam,
            'arqueo_url' => 'web/create?id=20&id_modelo=158&vista=tesoreria.arqueo_caja.create&teso_caja_id=' . $pdv->caja_default_id . '&pdv_id=' . $pdv->id,
            'factura_directa_url' => 'pos_factura/create?id=20&id_modelo=230&id_transaccion=47&pdv_id=' . $pdv->id . '&action=create',
        );
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
