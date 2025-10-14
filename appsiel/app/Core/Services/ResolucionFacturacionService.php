<?php

namespace App\Core\Services;

class ResolucionFacturacionService
{  

    public function validate_resolucion_facturacion( $tipo_doc_app, $core_empresa_id )
    {
        if( $tipo_doc_app == null )
        {
            return (object)[
                'status' => 'error',
                'message' => 'No hay un Tipo de Documento configurado para Facturas Electrónicas.'
            ];
        }

        $msj_resolucion_facturacion = '';
        $resolucion_facturacion = $tipo_doc_app->resolucion_facturacion->last();

        if( $resolucion_facturacion == null )
        {
            return (object)[
                'status' => 'error',
                'message' => 'No hay una Resolución asignada al tipo de documento: ' . $tipo_doc_app->descripcion . ' ('. $tipo_doc_app->prefijo . ')'
            ];
        }

        ////////////////////////////////////////
        $dias_pendientes = diferencia_en_dias_entre_fechas( date('Y-m-d'), $resolucion_facturacion->fecha_expiracion );

        if( $dias_pendientes <= 0 )
        {
            $msj_resolucion_facturacion = 'La resolución del documento de facturación electrónica ' . $tipo_doc_app->prefijo . ' ya expiró. Fecha límite: ' . $resolucion_facturacion->fecha_expiracion . '. Debe actualizar la resolución de facturación para que pueda seguir facturando.';

            return (object)[
                'status' => 'error',
                'message' => $msj_resolucion_facturacion
            ];

        }
        
        if( $dias_pendientes < 7 )
        {
            $msj_resolucion_facturacion = 'La resolución del documento de facturación electrónica ' .$tipo_doc_app->prefijo . ' está a punto de expirar. Fecha límite: ' . $resolucion_facturacion->fecha_expiracion . ' (' . $dias_pendientes . ' días restantes). Debe actualizar la resolución de facturación. Una vez vencida NO podrá seguir facturando.';

            return (object)[
                'status' => 'warning',
                'message' => $msj_resolucion_facturacion
            ];
        }
        
        $consecutivo_actual = $tipo_doc_app->get_consecutivo_actual($core_empresa_id, $tipo_doc_app->id);

        
        if( $consecutivo_actual >= $resolucion_facturacion->numero_fact_final )
        {
            $msj_resolucion_facturacion = 'La resolución del documento de facturación electrónica ' . $tipo_doc_app->prefijo . ' ya alcanzó el consecutivo máximo de facturas permitidas. Límite de consecutivos: ' . $resolucion_facturacion->numero_fact_final . '. Consecutivo actual: ' . $consecutivo_actual . '. Debe actualizar la resolución de facturación para que pueda seguir facturando.';

            return (object)[
                'status' => 'error',
                'message' => $msj_resolucion_facturacion
            ];
        }
        
        $cantidad_facturas_restantes = $resolucion_facturacion->numero_fact_final - $consecutivo_actual;

        if( $cantidad_facturas_restantes <= (int)config('facturacion_electronica.cantidad_facturas_restantes_aviso_resolucion') )
        {
            $msj_resolucion_facturacion = 'La resolución del documento de facturación electrónica ' . $tipo_doc_app->prefijo . ' está a punto de alcanzar el consecutivo máximo de facturas permitidas. Límite de consecutivos: ' . $resolucion_facturacion->numero_fact_final . '. Consecutivo actual: ' . $consecutivo_actual . '. Debe actualizar la resolución de facturación. Una vez alcanzado el límite de consecutivos NO podrá seguir facturando. Cantidad restante de consecutivos: ' . $cantidad_facturas_restantes;

            return (object)[
                'status' => 'warning',
                'message' => $msj_resolucion_facturacion
            ];
        }

        return (object)[
            'status' => 'success',
            'message' => $msj_resolucion_facturacion
        ];
    }
}