<?php

namespace App\Tesoreria;

use DB;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoMotivo;

use App\Contabilidad\ContabMovimiento;
use Illuminate\Support\Facades\Auth;

class RegistroDeTransferenciaConsignacion extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado, $pdv_id = null )
    {
        $lineas_registros = json_decode( $json_lineas_registros );

        if( !is_array($lineas_registros) )
        {
            return false;
        }

        // tableToJSON puede incluir el pie de totales. Se filtra por el campo
        // que identifica una línea real para no eliminar accidentalmente el
        // último pago cuando el pie no sea incluido por el navegador.
        $lineas_registros = array_values(array_filter($lineas_registros, function ($linea) {
            return is_object($linea)
                && isset($linea->tipo_operacion_id_transferencia_consignacion)
                && trim((string)$linea->tipo_operacion_id_transferencia_consignacion) !== '';
        }));

        $cantidad = count($lineas_registros);
        if ( $cantidad == 0 )
        {
            return false;
        }

        $teso_medio_recaudo_id = TesoMedioRecaudo::get_id_por_tipo_registro('transferencia_consignacion');
        $medio_recaudo = TesoMedioRecaudo::find($teso_medio_recaudo_id);
        if (is_null($medio_recaudo)) {
            throw new \Exception('El medio de recaudo para Transferencia/Consignación no existe.');
        }

        $valor_linea = 0;
        $valor_debito = 0;
        $valor_credito = 0;
        for ($i=0; $i < $cantidad; $i++) 
        {
            $linea = $lineas_registros[$i];
            $motivo_id = isset($linea->teso_motivo_id_transferencia_consignacion)
                ? (int)$linea->teso_motivo_id_transferencia_consignacion
                : 0;
            $cuenta_id = isset($linea->banco_id_transferencia_consignacion)
                ? (int)$linea->banco_id_transferencia_consignacion
                : 0;
            $valor = isset($linea->valor_transferencia_consignacion)
                ? $linea->valor_transferencia_consignacion
                : null;
            $tipo_operacion = trim((string)$linea->tipo_operacion_id_transferencia_consignacion);

            if ($tipo_operacion === '') {
                throw new \Exception('Debe seleccionar el tipo de operación de la transferencia.');
            }
            if (!is_numeric($valor) || (float)$valor <= 0) {
                throw new \Exception('El valor de la transferencia debe ser mayor que cero.');
            }

            $motivo = TesoMotivo::where('id', $motivo_id)
                ->where('core_empresa_id', (int)$doc_encabezado->core_empresa_id)
                ->where('estado', 'Activo')
                ->first();
            if (is_null($motivo)) {
                throw new \Exception('El motivo de la transferencia no existe, no está activo o pertenece a otra empresa.');
            }
            if ((string)$motivo->teso_tipo_motivo !== $tipo_operacion) {
                throw new \Exception('El motivo seleccionado no corresponde al tipo de operación de la transferencia.');
            }

            $cuenta_bancaria = TesoCuentaBancaria::where('id', $cuenta_id)
                ->where('core_empresa_id', (int)$doc_encabezado->core_empresa_id)
                ->where('estado', 'Activo')
                ->first();
            if (is_null($cuenta_bancaria)) {
                throw new \Exception('La cuenta bancaria seleccionada no existe, no está activa o pertenece a otra empresa.');
            }
            if (!$medio_recaudo->tiene_cuenta_bancaria_destino($cuenta_id)) {
                throw new \Exception('La cuenta bancaria seleccionada no está asociada al medio de recaudo Transferencia/Consignación.');
            }
            if (Auth::check() && !TesoCuentaBancaria::es_permitida_para_usuario($cuenta_id)) {
                throw new \Exception('El usuario no tiene permiso para usar la cuenta bancaria seleccionada.');
            }
            if ((int)$cuenta_bancaria->contab_cuenta_id <= 0) {
                throw new \Exception('La cuenta bancaria seleccionada no tiene una cuenta contable asociada.');
            }

            switch ( $motivo->movimiento )
            {
                case 'entrada':
                    $valor_linea = (float)$valor;
                    $valor_debito = (float)$valor;
                    $valor_credito = 0;
                    break;

                case 'salida':
                    $valor_linea = (float)$valor * -1;
                    $valor_debito = 0;
                    $valor_credito = (float)$valor;
                    break;
                
                default:
                    throw new \Exception('El motivo seleccionado no define si la transferencia es de entrada o salida.');
            }

            $detalle_operacion = isset($linea->detalle_operacion) ? $linea->detalle_operacion : '';

            $datos = [
                        'teso_encabezado_id' => $doc_encabezado->id,
                        'teso_motivo_id' => $motivo_id,
                        'teso_medio_recaudo_id' => $teso_medio_recaudo_id,
                        'teso_caja_id' => 0,
                        'teso_cuenta_bancaria_id' => $cuenta_id,
                        'detalle_operacion' => $detalle_operacion,
                        'valor' => abs( $valor_linea )
                    ] + $doc_encabezado->toArray();
            
            TesoDocRegistro::create( $datos );

            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $detalle_operacion;
            $numero_comprobante = isset($linea->numero_comprobante_transferencia_consignacion)
                ? trim((string)$linea->numero_comprobante_transferencia_consignacion)
                : '';
            $datos['documento_soporte'] = 'Comprobante numero ' . $numero_comprobante;
            $datos['pdv_id'] = $pdv_id;
            TesoMovimiento::create( $datos );

            // Contabilizar
            $datos['tipo_transaccion'] = '';
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
