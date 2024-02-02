<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;
use App\Http\Controllers\Core\TransaccionController;


// Modelos
use App\Core\TipoDocApp;

use App\Nomina\Services\ContabilizacionProvisionNomina;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class ContabilizacionProvisionController extends TransaccionController
{
    public function contabilizar( Request $request )
    {

        $servicio_contabilizacion = new ContabilizacionProvisionNomina( $request->fecha_final_promedios, $request->core_tipo_doc_app_id );

        $contab_proceso = $servicio_contabilizacion->get_estado( $request->fecha_final_promedios );
        if (  $contab_proceso != null )
        {
            $document_header = $servicio_contabilizacion->get_document_header( $contab_proceso->core_tipo_transaccion_id, $contab_proceso->core_tipo_doc_app_id, $contab_proceso->consecutivo  );
            return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_provision_contabilizado', [ 'encabezado_doc' => $document_header, 'accion' => 'validar' ] )->render();
        }

        $consecutivo = 0;
        $tipo_documento_app = TipoDocApp::find( (int)$request->core_tipo_doc_app_id );
        $encabezado_doc = (object)[ 
                                    'fecha' => $request->fecha_final_promedios,
                                    'tipo_documento_app' => (object)[ 'prefijo' => $tipo_documento_app->prefijo],
                                    'consecutivo' => $consecutivo,
                                    'descripcion' => ''
                                ];

        $lineas_html_movimiento_contable = $servicio_contabilizacion->get_lineas_html_movimiento_contable();

        if ( $request->almacenar_registros && !empty( $servicio_contabilizacion->movimiento_contabilizar->first() ) )
        {
            // Seleccionamos el consecutivo actual (si no existe, se crea) y le sumamos 1
            $consecutivo = TipoDocApp::get_consecutivo_actual( Auth::user()->empresa_id, (int)$request->core_tipo_doc_app_id) + 1;

            // Se incementa el consecutivo para ese tipo de documento y la empresa
            TipoDocApp::aumentar_consecutivo( Auth::user()->empresa_id, (int)$request->core_tipo_doc_app_id);

            // Contabilizar
            $encabezado_doc = $servicio_contabilizacion->crear_encabezado_documento_contable( $consecutivo );
            //dd($encabezado_doc);
            $servicio_contabilizacion->almacenar_movimiento_contable( $consecutivo );
        }

        $vista = View::make( 'nomina.procesos.incluir.resultado_contabilizacion_provision', [ 'encabezado_doc' => $encabezado_doc, 'lineas_tabla' => $lineas_html_movimiento_contable, 'valor_debito_total' => $servicio_contabilizacion->valor_debito_total, 'valor_credito_total' => $servicio_contabilizacion->valor_credito_total, 'contabilizado' => $request->almacenar_registros ] )->render();
        
        return $vista;
    }

    public function retirar( $fecha_final_promedios )
    {
        $servicio_contabilizacion = new ContabilizacionProvisionNomina( 0, 0 );

        $resultado_retiro = $servicio_contabilizacion->retirar_contabilizacion( $fecha_final_promedios );

        $mensaje = 'El documento contable fue retirado exitosamente.';
        $clase = 'success';

        if( $resultado_retiro <> 'ok' )
        {
            $mensaje = $resultado_retiro;
            $clase = 'warning';
        }

        $encabezado_doc = (object)[ 
                                    'fecha' => $fecha_final_promedios,
                                    'tipo_documento_app' => (object)[ 'prefijo' => ''],
                                    'consecutivo' => 0,
                                    'descripcion' => ''
                                ];

        return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_provision_contabilizado', [ 'encabezado_doc' => $encabezado_doc, 'accion' => 'retirar', 'mensaje' => $mensaje, 'clase' => $clase ] )->render();   
    }    
}