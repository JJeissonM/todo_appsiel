<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelReservation;
use App\Hotel\Support\HotelBreadcrumb;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class HotelReservationController extends Controller
{
    public function __construct()
    {
        HotelBreadcrumb::ensureContext('App\\Hotel\\HotelReservation');
    }

    public function cancel($id)
    {
        $reservation = HotelReservation::where('empresa_id', Auth::user()->empresa_id)->where('id', $id)->firstOrFail();

        try {
            $reservation->cancel();
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', $e->getMessage());
        }

        return redirect()->back()->with('flash_message', 'Reserva anulada correctamente.');
    }
}
