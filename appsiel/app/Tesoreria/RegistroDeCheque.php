<?php

namespace App\Tesoreria;

use DB;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\ControlCheque;

use App\Contabilidad\ContabMovimiento;

class RegistroDeCheque extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado, $teso_medio_recaudo_id, $estado )
    {
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_cheque );

            switch ( $motivo->movimiento )
            {
                case 'entrada':
                    $valor_linea = (float)$lineas_registros[$i]->valor_cheque;
                    $valor_debito = (float)$lineas_registros[$i]->valor_cheque;
                    $valor_credito = 0;
                    break;

                case 'salida':
                    $valor_linea = (float)$lineas_registros[$i]->valor_cheque * -1;
                    $valor_debito = 0;
                    $valor_credito = (float)$lineas_registros[$i]->valor_cheque;
                    break;
                
                default:
                    # code...
                    break;
            }
                
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_cheque;

            $datos = [
                        'fuente' => 'de_tercero',
                        'tercero_id' => $doc_encabezado->core_tercero_id,
                        'fecha_emision' => $lineas_registros[$i]->fecha_emision,
                        'fecha_cobro' => $lineas_registros[$i]->fecha_cobro,
                        'numero_cheque' => $lineas_registros[$i]->numero_cheque,
                        'referencia_cheque' => $lineas_registros[$i]->referencia_cheque,
                        'entidad_financiera_id' => $lineas_registros[$i]->entidad_financiera_id,
                        'valor' => abs( $valor_linea ),
                        'core_tipo_transaccion_id_origen' => $doc_encabezado->core_tipo_transaccion_id,
                        'core_tipo_doc_app_id_origen' => $doc_encabezado->core_tipo_doc_app_id,
                        'teso_caja_id' => $lineas_registros[$i]->caja_id_cheque,
                        'estado' => $estado
                    ] + $doc_encabezado->toArray();

            ControlCheque::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['teso_motivo_id'] = (int)$lineas_registros[$i]->teso_motivo_id_cheque;
            $datos['documento_soporte'] = 'Cheque número ' . $lineas_registros[$i]->numero_cheque;
            TesoMovimiento::create( $datos );

            // Contabilizar
            $caja = TesoCaja::find( (int)$lineas_registros[$i]->caja_id_cheque );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $caja->contab_cuenta_id, $tipo_operacion, $valor_debito, $valor_credito );

            // Contabilizar Contrapartida (Si no es movimiento de cartera)
            // La contabilizacion para la Cartera se hace en el metodo almacenar_registros_cartera()
            if ( $tipo_operacion != 'Recaudo cartera' && $tipo_operacion != 'Pago proveedores' )
            {
                $movimiento_contable = new ContabMovimiento();
                // Se invierten los valores Debito y Credito de arriba
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, $valor_credito, $valor_debito );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, abs( $valor_linea ) );
        }
    }
}
