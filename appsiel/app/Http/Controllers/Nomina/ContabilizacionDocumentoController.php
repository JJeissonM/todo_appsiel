<?php

namespace App\Http\Controllers\Nomina;

use Illuminate\Http\Request;

use App\Http\Controllers\Core\TransaccionController;

use App\Nomina\Services\ContabilizacionDocumentoNomina;
use App\Nomina\NomDocEncabezado;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class ContabilizacionDocumentoController extends TransaccionController
{

    public function contabilizar( Request $request )
    {
        $documento = NomDocEncabezado::find((int)$request->nom_doc_encabezado_id);
        if (is_null($documento)) {
            return $this->respuesta_error('El documento de nómina seleccionado no existe.');
        }

        if (Auth::check() && (int)$documento->core_empresa_id !== (int)Auth::user()->empresa_id) {
            return $this->respuesta_error('No está autorizado para contabilizar este documento de nómina.');
        }

        try {
            $servicio_contabilizacion = new ContabilizacionDocumentoNomina($documento->id);
        } catch (\Throwable $e) {
            Log::error('Nómina: no fue posible inicializar la contabilización.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respuesta_error('Ocurrió un error al iniciar el proceso. Consulte el registro de la aplicación.');
        }

        if ( $servicio_contabilizacion->get_estado() == 'contabilizado' )
        {
            return View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento_contabilizado', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'accion' => 'validar' ] )->render();
        }

        try {
            $lineas_html_movimiento_contable = $servicio_contabilizacion->get_lineas_html_movimiento_contable();
        } catch (\RuntimeException $e) {
            Log::warning('Nómina: inconsistencia preparando la contabilización.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
            ]);

            return $this->respuesta_error($e->getMessage());
        } catch (\Throwable $e) {
            Log::error('Nómina: no fue posible preparar la contabilización.', [
                'documento_id' => $documento->id,
                'error' => $e->getMessage(),
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->respuesta_error('Ocurrió un error al preparar la contabilización. Consulte el registro de la aplicación.');
        }

        if (empty($lineas_html_movimiento_contable)) {
            return $this->respuesta_error('El documento no tiene registros de nómina con valores para contabilizar.');
        }

        if ( $request->almacenar_registros )
        {
            if ( $servicio_contabilizacion->encabezado_doc->estado != NomDocEncabezado::ESTADO_ACTIVO ) {
                return '<div class="alert alert-warning">El documento de nómina no puede contabilizarse porque no está en estado Activo.</div>';
            }

            if ($this->hay_errores_equivalencias_contables($lineas_html_movimiento_contable)) {
                $advertencia = View::make('nomina.procesos.incluir.errores_equivalencia_contable')->render();
                $detalle = View::make('nomina.procesos.incluir.resultado_contabilizacion_documento', [
                    'encabezado_doc' => $servicio_contabilizacion->encabezado_doc,
                    'lineas_tabla' => $lineas_html_movimiento_contable,
                    'valor_debito_total' => $servicio_contabilizacion->valor_debito_total,
                    'valor_credito_total' => $servicio_contabilizacion->valor_credito_total,
                    'contabilizado' => false,
                ])->render();

                return $advertencia . $detalle;
            }
            // Contabilizar y generar movimientos de CxC y CxP
            try {
                $servicio_contabilizacion->almacenar_movimiento_contable();
            } catch (\RuntimeException $e) {
                Log::warning('Nómina: contabilización rechazada.', [
                    'documento_id' => $documento->id,
                    'error' => $e->getMessage(),
                ]);

                return $this->respuesta_error('No se almacenó ningún movimiento contable. ' . $e->getMessage());
            } catch (\Throwable $e) {
                Log::error('Nómina: error almacenando la contabilización.', [
                    'documento_id' => $documento->id,
                    'error' => $e->getMessage(),
                    'exception_class' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                return $this->respuesta_error('No se almacenó ningún movimiento contable. Consulte el registro de la aplicación.');
            }
            //$servicio_contabilizacion->encabezado_doc->estado = 'Cerrado';
            //$servicio_contabilizacion->encabezado_doc->save();
        }
        
        $vista = View::make( 'nomina.procesos.incluir.resultado_contabilizacion_documento', [ 'encabezado_doc' => $servicio_contabilizacion->encabezado_doc, 'lineas_tabla' => $lineas_html_movimiento_contable, 'valor_debito_total' => $servicio_contabilizacion->valor_debito_total, 'valor_credito_total' => $servicio_contabilizacion->valor_credito_total, 'contabilizado' => $request->almacenar_registros ] )->render();
        
        return $vista;
    }

    protected function respuesta_error($mensaje)
    {
        return '<div class="alert alert-danger"><strong>No fue posible contabilizar.</strong><br>' . e($mensaje) . '</div>';
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
