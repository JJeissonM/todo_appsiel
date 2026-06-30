<?php

namespace App\Http\Controllers\Hotel;

use App\Hotel\HotelStay;
use App\Hotel\HotelStayGuest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelStayGuestController extends Controller
{
    public function store(Request $request, $id)
    {
        $stay = $this->findStay($id);
        if ($stay->status != HotelStay::STATUS_ACTIVA) {
            return redirect()->back()->with('mensaje_error', 'Solo se pueden agregar huespedes a estadias activas.');
        }

        $this->validate($request, array('cliente_id' => 'required|exists:vtas_clientes,id'));

        try {
            HotelStayGuest::create(array(
                'empresa_id' => Auth::user()->empresa_id,
                'stay_id' => $stay->id,
                'cliente_id' => $request->cliente_id,
                'is_main_guest' => 0,
                'relationship' => $request->relationship,
            ));
        } catch (\Exception $e) {
            return redirect()->back()->with('mensaje_error', 'No se pudo agregar el huesped. Verifique si ya esta registrado.');
        }

        return redirect('hotel/stays/' . $stay->id)->with('flash_message', 'Huesped agregado correctamente.');
    }

    public function destroy($id, $guestId)
    {
        $stay = $this->findStay($id);
        $guest = HotelStayGuest::where('empresa_id', Auth::user()->empresa_id)->where('stay_id', $stay->id)->where('id', $guestId)->firstOrFail();

        if ((int)$guest->is_main_guest == 1) {
            return redirect()->back()->with('mensaje_error', 'No se puede eliminar el huesped principal.');
        }

        $guest->delete();
        return redirect('hotel/stays/' . $stay->id)->with('flash_message', 'Huesped eliminado correctamente.');
    }

    private function findStay($id)
    {
        return HotelStay::where('empresa_id', Auth::user()->empresa_id)->where('id', $id)->firstOrFail();
    }
}
