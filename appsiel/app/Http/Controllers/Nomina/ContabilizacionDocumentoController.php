<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;

use App\Nomina\Services\ContabilizacionDocumentoNomina;
use Illuminate\Support\Facades\View;

class ContabilizacionDocumentoController extends TransaccionController
{

    public function contabilizar( Request $request )
    {
        $servicio_contabilizacion = new ContabilizacionDocumentoNomina( (int)$request->nom_doc_encabezado_id );

        if ( $servicio_contabilizacion->get_estado() == 'contabilizado' )
        {
            return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento_contabilizado', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'accion' => 'validar' ] )->render();
        }

        $lineas_html_movimiento_contable = $servicio_contabilizacion->get_lineas_html_movimiento_contable();

        if ( $request->almacenar_registros )
        {
            if ($this->hay_errores_equivalencias_contables($lineas_html_movimiento_contable)) {
                return View::make( 'nomina.procesos.incluir.errores_equivalencia_contable')->render();
            }
            // Contabilizar y generar movimientos de CxC y CxP
            $servicio_contabilizacion->almacenar_movimiento_contable();
            //$servicio_contabilizacion->encabezado_doc->estado = 'Cerrado';
            //$servicio_contabilizacion->encabezado_doc->save();
        }
        
        $vista = View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'lineas_tabla' => $lineas_html_movimiento_contable, 'valor_debito_total' => $servicio_contabilizacion->valor_debito_total, 'valor_credito_total' => $servicio_contabilizacion->valor_credito_total, 'contabilizado' => $request->almacenar_registros ] )->render();
        
        return $vista;
    }


    public function hay_errores_equivalencias_contables($lineas_html_movimiento_contable)
    {
        foreach ($lineas_html_movimiento_contable as $linea) {
            if ($linea->error) {
                return true;
            }
        }
        return false;
    }

    public function retirar( $doc_encabezado_id )
    {
        $servicio_contabilizacion = new ContabilizacionDocumentoNomina( (int)$doc_encabezado_id );

        $resultado_retiro = $servicio_contabilizacion->retirar_contabilizacion();

        $mensaje = 'El documento de nómina fue retirado exitosamente de la contabilidad.';
        $clase = 'success';

        if( $resultado_retiro <> 'ok' )
        {
            $mensaje = $resultado_retiro;
            $clase = 'warning';
        }

        return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento_contabilizado', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'accion' => 'retirar', 'mensaje' => $mensaje, 'clase' => $clase ] )->render();   
    }    
}