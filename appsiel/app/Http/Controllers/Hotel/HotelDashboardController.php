<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Sistema\ModeloController;
use App\Sistema\Modelo;
use App\Sistema\Services\ModeloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelDashboardController extends Controller
{
    public function __construct()
    {
        HotelBreadcrumb::ensureContext('App\\Hotel\\HotelRoom');
    }

    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;

        $query = HotelRoom::where('empresa_id', $empresaId)
            ->with('product', 'activeStay.orders', 'activeStay.mainGuest.tercero')
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
        $miga_pan = HotelBreadcrumb::dashboard('Habitaciones');
        $appId = HotelBreadcrumb::appId();
        $roomModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelRoom');
        $stayModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelStay');
        $orderModelId = HotelBreadcrumb::modelId('App\\Hotel\\HotelOrderHeader');
        $guestModelId = 138;
        $roomIndexUrl = HotelBreadcrumb::crudIndexUrl('App\\Hotel\\HotelRoom');
        $roomCreateUrl = HotelBreadcrumb::crudCreateUrl('App\\Hotel\\HotelRoom');
        $guestCreateUrl = HotelBreadcrumb::crudCreateUrl('App\\Ventas\\Cliente');
        $guestFormCreate = $this->guestFormCreate($guestModelId);

        return view('hotel.index', compact('rooms', 'floors', 'statuses', 'summary', 'miga_pan', 'appId', 'roomModelId', 'stayModelId', 'orderModelId', 'guestModelId', 'roomIndexUrl', 'roomCreateUrl','guestCreateUrl', 'guestFormCreate'));
    }

    private function summary($empresaId)
    {
        $summary = array();
        foreach (HotelRoom::statuses() as $status) {
            $summary[$status] = HotelRoom::where('empresa_id', $empresaId)->where('status', $status)->count();
        }

        return $summary;
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
