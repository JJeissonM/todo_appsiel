<?php 

namespace App\Ventas\Services;

use App\Contabilidad\ContabMovimiento;
use App\Ventas\VtasDocEncabezado;
use App\Ventas\VtasDocRegistro;
use Illuminate\Support\Facades\Auth;

class OrderServices
{
    /*
        Proceso de eliminar PEDIDO
        Se eliminan los registros de:
            - se actualiza el estado a Anulado en vtas_doc_registros y vtas_doc_encabezados
    */
    public function cancel_order( $pedido)
    {
        // Se marcan como anulados todos los registros del documento
        VtasDocRegistro::where('vtas_doc_encabezado_id', $pedido->id)->update(['estado' => 'Anulado']);

        // Se revive la cotizacion
        $cotizacion = VtasDocEncabezado::find( $pedido->ventas_doc_relacionado_id );
        if ( !is_null($cotizacion) )
        {
            $pedido->ventas_doc_relacionado_id = 0;
            $cotizacion->estado = 'Pendiente';
            $cotizacion->save();
        }

        // Se marca como anulado al pedido
        $pedido->estado = 'Anulado';
        $pedido->modificado_por = Auth::user()->email;
        $pedido->save();
    }
        
}