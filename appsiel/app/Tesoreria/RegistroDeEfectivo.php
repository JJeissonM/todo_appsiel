<?php

namespace App\Tesoreria;

use DB;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;

class RegistroDeEfectivo extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 1; // Efectivo
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_efectivo );

            switch ( $motivo->movimiento )
            {
                case 'entrada':
                    $valor_linea = (float)$lineas_registros[$i]->valor_efectivo;
                    $valor_debito = (float)$lineas_registros[$i]->valor_efectivo;
                    $valor_credito = 0;
                    break;

                case 'salida':
                    $valor_linea = (float)$lineas_registros[$i]->valor_efectivo * -1;
                    $valor_debito = 0;
                    $valor_credito = (float)$lineas_registros[$i]->valor_efectivo;
                    break;
                
                default:
                    # code...
                    break;
            }
                
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_efectivo;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_efectivo,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => (int)$lineas_registros[$i]->caja_id_efectivo,
                        'teso_cuenta_bancaria_id' => 0,
                        'detalle_operacion' => $tipo_operacion,
                        'valor' => abs( $valor_linea )
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            TesoMovimiento::create( $datos );

            // Contabilizar
            $datos['tipo_transaccion'] = '';
            $caja = TesoCaja::find( (int)$lineas_registros[$i]->caja_id_efectivo );
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
