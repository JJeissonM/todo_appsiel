<?php

namespace App\Core\Services;

use App\Core\Empresa;
use App\Core\TipoDocApp;
use Illuminate\Support\Facades\Auth;

class ResolucionFacturacionService
{  

    public function validate_resolucion_facturacion( TipoDocApp $tipo_doc_app, $core_empresa_id )
    {
        $msj_resolucion_facturacion = '';
        $resolucion_facturacion = $tipo_doc_app->resolucion_facturacion->last();
        if ( $resolucion_facturacion != null )
        {
            $dias_pendientes = diferencia_en_dias_entre_fechas( date('Y-m-d'), $resolucion_facturacion->fecha_expiracion );

            if( $dias_pendientes <= 0 )
            {
                $msj_resolucion_facturacion = 'La resolución del documento de facturación ' . $tipo_doc_app->prefijo . ' ya expiró. Fecha límite: ' . $resolucion_facturacion->fecha_expiracion . '. Debe actualizar la resolución de facturación para poder seguir facturando.';

                return (object)[
                    'status' => 'error',
                    'message' => $msj_resolucion_facturacion
                ];

            }
            
            if( $dias_pendientes < 7 )
            {
                $msj_resolucion_facturacion = 'La resolución del documento de facturación ' .$tipo_doc_app->prefijo . ' está a punto de expirar. Fecha límite: ' . $resolucion_facturacion->fecha_expiracion . ' (' . $dias_pendientes . ' días restantes). Debe actualizar la resolución de facturación. Una vez vencida NO podrá seguir facturando.';

                return (object)[
                    'status' => 'warning',
                    'message' => $msj_resolucion_facturacion
                ];
            }
            
            $consecutivo_actual = $tipo_doc_app->get_consecutivo_actual($core_empresa_id, $tipo_doc_app->id);

            $cantidad_facturas_restantes = $resolucion_facturacion->numero_fact_final - $consecutivo_actual;

            if( $cantidad_facturas_restantes <= (int)config('ventas.cantidad_facturas_restantes_aviso_resolucion') )
            {
                $msj_resolucion_facturacion = 'La resolución del documento de facturación ' . $tipo_doc_app->prefijo . ' está a punto de alcanzar el consecutivo máximo de facturas permitidas. Límite de consecutivos: ' . $resolucion_facturacion->numero_fact_final . '. Consecutivo actual: ' . $consecutivo_actual . '. Debe actualizar la resolución de facturación. Una vez alcanzado el límite de consecutivos NO podrá seguir facturando.';

                return (object)[
                    'status' => 'warning',
                    'message' => $msj_resolucion_facturacion
                ];
            }
        }

        return (object)[
            'status' => 'success',
            'message' => $msj_resolucion_facturacion
        ];
    }
}