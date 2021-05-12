<?php

namespace App\Tesoreria;

use DB;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;

class RegistroDeTarjetaCredito extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado )
    {
        $teso_medio_recaudo_id = 3; // Tarjeta crédito
        $lineas_registros = json_decode( $json_lineas_registros );

        if( is_null($lineas_registros) )
        {
            return false;
        }

        array_pop($lineas_registros); // Elimina ultimo elemento del array
        
        $cantidad = count($lineas_registros);
        for ($i=0; $i < $cantidad; $i++) 
        {
            $valor_linea = (float)$lineas_registros[$i]->valor_tarjeta_credito;
            $tipo_operacion = $lineas_registros[$i]->tipo_operacion_id_tarjeta_credito;

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_credito,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => (int)$lineas_registros[$i]->banco_id_tarjeta_credito,
                        'detalle_operacion' => $tipo_operacion . '. Comprobante número ' . $lineas_registros[$i]->numero_comprobante_tarjeta_credito,
                        'valor' => $valor_linea
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Comprobante numero ' . $lineas_registros[$i]->numero_comprobante_tarjeta_credito;
            TesoMovimiento::create( $datos );

            // Contabilizar DB
            $cuenta_bancaria = TesoCuentaBancaria::find( (int)$lineas_registros[$i]->banco_id_tarjeta_credito );
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $cuenta_bancaria->contab_cuenta_id, $tipo_operacion, $valor_linea, 0 );

            // Contabilizar CR
            // La contabilizacion CR para Recaudo Cartera se hace en el metodo almacenar_registros_cxc()
            if ( $tipo_operacion != 'Recaudo cartera' && $tipo_operacion != 'Pago proveedores' )
            { 
                $motivo = TesoMotivo::find( (int)$lineas_registros[$i]->teso_motivo_id_tarjeta_credito );
                $movimiento_contable = new ContabMovimiento();
                $movimiento_contable->contabilizar_linea_registro( $datos, $motivo->contab_cuenta_id, $tipo_operacion, 0, $valor_linea );
            }

            $this->transacciones_adicionales( $datos, $tipo_operacion, $valor_linea );
        }
    }
}
