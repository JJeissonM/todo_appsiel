<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelRoom;
use App\Hotel\HotelStay;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class HotelReportController extends Controller
{
    public function rooms(Request $request)
    {
        $rooms = HotelRoom::where('empresa_id', Auth::user()->empresa_id)
            ->with('product')
            ->orderBy('room_number')
            ->get();

        $html = view('hotel.reports.rooms', compact('rooms'))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    public function stays(Request $request)
    {
        $query = HotelStay::where('empresa_id', Auth::user()->empresa_id)
            ->with('room', 'mainGuest.tercero')
            ->orderBy('check_in_at', 'DESC');

        if ($request->fecha_desde != '') {
            $query->where('check_in_at', '>=', $request->fecha_desde . ' 00:00:00');
        }

        if ($request->fecha_hasta != '') {
            $query->where('check_in_at', '<=', $request->fecha_hasta . ' 23:59:59');
        }

        $stays = $query->get();

        $html = view('hotel.reports.stays', compact('stays'))->render();
        $this->cacheReport($request, $html);

        return $html;
    }

    private function cacheReport(Request $request, $html)
    {
        if ($request->reporte_id != '') {
            Cache::put('pdf_reporte_' . $request->reporte_id, $html, 60);
        }
    }
}
