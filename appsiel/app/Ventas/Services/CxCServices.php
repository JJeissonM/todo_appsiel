<?php 

namespace App\Ventas\Services;

use App\CxC\CxcMovimiento;

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
}