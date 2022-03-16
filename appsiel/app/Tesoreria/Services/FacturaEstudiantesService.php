<?php 

namespace App\Tesoreria\Services;

use App\Matriculas\FacturaAuxEstudiante;

use App\Tesoreria\TesoRecaudosLibreta;
use App\Ventas\VtasDocEncabezado;

class FacturaEstudiantesService
{
    public function registrar_recaudo_cartera_estudiante( $doc_encabezado_recaudo, $registro_cxc_pendiente, $abono  )
    {
        $factura = VtasDocEncabezado::where([
                                                [ 'core_tipo_transaccion_id','=', $registro_cxc_pendiente->core_tipo_transaccion_id ],
                                                [ 'core_tipo_doc_app_id','=', $registro_cxc_pendiente->core_tipo_doc_app_id ],
                                                [ 'consecutivo','=', $registro_cxc_pendiente->consecutivo ]
                                            ])->get()->first();

        if ( is_null($factura) )
        {
            return false;
        }

        $aux_factura = FacturaAuxEstudiante::where('vtas_doc_encabezado_id', $factura->id )->get()->first();

        if ( is_null($aux_factura) )
        {
            return false;
        }

        $teso_medio_recaudo_id = 1;
        if ($doc_encabezado_recaudo->teso_medio_recaudo_id!=null) {
            $teso_medio_recaudo_id = $doc_encabezado_recaudo->teso_medio_recaudo_id;
        }

        $recaudo = TesoRecaudosLibreta::create( [
                                    'core_tipo_transaccion_id' => (int)$doc_encabezado_recaudo->core_tipo_transaccion_id,
                                    'core_tipo_doc_app_id' => (int)$doc_encabezado_recaudo->core_tipo_doc_app_id,
                                    'consecutivo' => $doc_encabezado_recaudo->consecutivo,
                                    'id_libreta' => $aux_factura->cartera_estudiante->id_libreta,
                                    'id_cartera' => $aux_factura->cartera_estudiante_id,
                                    'concepto' => $aux_factura->cartera_estudiante->inv_producto_id,
                                    'fecha_recaudo' => $doc_encabezado_recaudo->fecha,
                                    'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                                    'cantidad_cuotas' => 1,
                                    'valor_recaudo' => $abono,
                                    'creado_por' => $doc_encabezado_recaudo->creado_por
                                ] );

        $recaudo->registro_cartera_estudiante->sumar_abono_registro_cartera_estudiante( $abono );
        $recaudo->libreta->actualizar_estado();
    }
}