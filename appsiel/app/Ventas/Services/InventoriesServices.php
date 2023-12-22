<?php 

namespace App\Ventas\Services;

use App\Inventarios\InvBodega;
use App\Ventas\Cliente;
use App\VentasPos\Pdv;
use Illuminate\Support\Facades\Auth;

class InventoriesServices
{    
    public function get_bodegas($estado = 'Activo')
    {
        return InvBodega::where([
            [ 'estado', '=', $estado]
        ])->get();
    }

    public function get_bodega_id($customer_id)
    {
        $user = Auth::user();

        $pdv_asociado = Pdv::where([
            ['cajero_default_id', '=', $user->id]
        ])->get()
        ->first();

        if ($pdv_asociado != null) {
            return $pdv_asociado->bodega_default_id;
        }

        $bodega_id = Cliente::find($customer_id)->inv_bodega_id;

        if ($bodega_id != null && $bodega_id != 0) {
            return $bodega_id;
        }

        return (int)config('ventas.inv_bodega_id');
    }        
}