<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelDashboardController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;

        $query = HotelRoom::where('empresa_id', $empresaId)
            ->with('product', 'activeStay.order', 'activeStay.mainGuest.tercero')
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

        return view('hotel.index', compact('rooms', 'floors', 'statuses', 'summary', 'miga_pan'));
    }

    private function summary($empresaId)
    {
        $summary = array();
        foreach (HotelRoom::statuses() as $status) {
            $summary[$status] = HotelRoom::where('empresa_id', $empresaId)->where('status', $status)->count();
        }

        return $summary;
    }
}
