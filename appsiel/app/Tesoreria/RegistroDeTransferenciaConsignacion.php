<?php

namespace App\Tesoreria;

use DB;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;

class RegistroDeTransferenciaConsignacion extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 4; // Banco (Consignación)
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_transferencia_consignacion );

            switch ( $motivo->movimiento )
            {
                case 'entrada':
                    $valor_linea = (float)$lineas_registros[$i]->valor_transferencia_consignacion;
                    $valor_debito = (float)$lineas_registros[$i]->valor_transferencia_consignacion;
                    $valor_credito = 0;
                    break;

                case 'salida':
                    $valor_linea = (float)$lineas_registros[$i]->valor_transferencia_consignacion * -1;
                    $valor_debito = 0;
                    $valor_credito = (float)$lineas_registros[$i]->valor_transferencia_consignacion;
                    break;
                
                default:
                    # code...
                    break;
            }

            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_transferencia_consignacion;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_transferencia_consignacion,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => (int)$lineas_registros[$i]->banco_id_transferencia_consignacion,
                        'detalle_operacion' => $tipo_operacion . '. Comprobante número ' . $lineas_registros[$i]->numero_comprobante_transferencia_consignacion,
                        'valor' => abs( $valor_linea )
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_transferencia_consignacion;
            TesoMovimiento::create( $datos );

            // Contabilizar
            $datos['tipo_transaccion'] = '';
            $cuenta_bancaria = TesoCuentaBancaria::find( (int)$lineas_registros[$i]->banco_id_transferencia_consignacion );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $cuenta_bancaria->contab_cuenta_id, $tipo_operacion, $valor_debito, $valor_credito );

            // Contabilizar Contrapartida (Si no es movimiento de cartera)
            // La contabilizacion para la Cartera se hace en el metodo almacenar_registros_cartera()
            if ( $tipo_operacion != 'recaudo-cartera' && $tipo_operacion != 'pago-proveedores' )
            {
                $movimiento_contable = new ContabMovimiento();
                // Se invierten los valores Debito y Credito de arriba
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, $valor_credito, $valor_debito );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, abs( $valor_linea ) );
        }
    }
}
