<?php 

namespace App\Ventas\Services;

use App\CxC\CxcMovimiento;
use App\CxC\Services\DocumentosPendientesCxC;
use Illuminate\Support\Facades\View;

class CxCServices
{    
    public function get_tabla_cartera_afavor_tercero($tercero_id, $fecha_doc)
    {
      // 1ro. Buscar documentos de cartera
      $movimiento_cxc = CxcMovimiento::get_documentos_tercero($tercero_id, $fecha_doc);
      
      $vista = 'show';
      return View::make('cxc.incluir.docs_cruce_afavor', compact('movimiento_cxc', 'vista') );
    }

    public function get_movimiento_documentos_pendientes_fecha_corte($tercero_id, $fecha)
    {
        $movimiento_cxc = (new DocumentosPendientesCxC())->get_movimiento_documentos_pendientes_fecha_corte($fecha, $tercero_id);

        return $movimiento_cxc->sum('saldo_pendiente');
    }
}