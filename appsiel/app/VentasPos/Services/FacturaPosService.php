<?php 

namespace App\VentasPos\Services;

use App\Core\Services\ResolucionFacturacionService;

class FacturaPosService
{
    public function get_msj_resolucion_facturacion( $pdv )
    {
        $obj_resolucion_facturacion = $this->get_obj_resolucion_facturacion( $pdv );

        $msj_resolucion_facturacion = '';
        $status = 'success';
        
        if ( $obj_resolucion_facturacion->status != 'success' )
        {
            $msj_resolucion_facturacion = $obj_resolucion_facturacion->message;
            $status = $obj_resolucion_facturacion->status;
        }

        return (object)[
            'status' => $status,
            'message' => $msj_resolucion_facturacion
        ];
    }

    public function get_obj_resolucion_facturacion( $pdv )
    {
        return (new ResolucionFacturacionService())->validate_resolucion_facturacion($pdv->tipo_doc_app, $pdv->core_empresa_id);
    }
}