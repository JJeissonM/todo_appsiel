<?php

namespace App\Http\Controllers\CxC;

use App\CxC\Services\OutstandingBalancesAcr;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;

class ProcesosController extends Controller
{
    public function pruebas()
    {
        $balance_acr_serv = new OutstandingBalancesAcr();

        $deadline = \Carbon\Carbon::parse( Input::get('deadline') )->format('Y-m-d');

        $movements = $balance_acr_serv->get_outstanding_balance_ofcontact_ondeadline( $deadline, Input::get('contact_id') );
        
        
        foreach ( $movements as $movement )
        {
            $movement->show = 1;
            // Para NO mostrar saldos con saldo pendientes cero
            if ( $movement->saldo_pendiente == 0)// && $movement->id != 0 
            {
                $movement->show = 0;
            }else{
                if ( $movement->saldo_pendiente >= -1 && $movement->saldo_pendiente <= 1)
                {
                    return response()->json([
                        'movement' => $movement,
                        'movement_document_header' => $movement->get_movement_document(),
                        'payments' => $balance_acr_serv->get_acr_movement_payments($movement, $deadline)
                    ]);
                }
            }
        }

        return response()->json($movements);

    }
}