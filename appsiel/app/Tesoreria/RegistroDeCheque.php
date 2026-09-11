<?php

namespace App\Tesoreria;

use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\ControlCheque;
use App\Tesoreria\Services\ChequePaymentService;

use App\Contabilidad\ContabMovimiento;
use Illuminate\Support\Facades\Auth;

class RegistroDeCheque extends TesoDocEncabezado
{
    public function almacenar_registros( $json_lineas_registros, $doc_encabezado, $teso_medio_recaudo_id, $estado, $fuente, $pdv_id = null )
    {
        $lineas_registros = json_decode( $json_lineas_registros );

        if( !is_array($lineas_registros) )
        {
            return false;
        }

        // tableToJSON puede incluir el pie de totales. Solo se procesan filas
        // que representen realmente un cheque.
        $lineas_registros = array_values(array_filter($lineas_registros, function ($linea) {
            return is_object($linea)
                && isset($linea->tipo_operacion_id_cheque)
                && trim((string)$linea->tipo_operacion_id_cheque) !== ''
                && isset($linea->valor_cheque)
                && (float)$linea->valor_cheque > 0;
        }));

        if ( count($lineas_registros) == 0 )
        {
            return false;
        }

        if (!is_numeric($teso_medio_recaudo_id)) {
            $teso_medio_recaudo_id = TesoMedioRecaudo::get_id_por_tipo_registro($teso_medio_recaudo_id);
        }

        $chequePaymentService = new ChequePaymentService();
        $usarChequera = $fuente === 'propio' && ChequePaymentService::usaChequera();

        foreach ($lineas_registros as $linea)
        {
            $motivo = TesoMotivo::find( (int)$linea->teso_motivo_id_cheque );
            if (is_null($motivo)) {
                throw new \Exception('El motivo del cheque no existe.');
            }

            switch ( $motivo->movimiento )
            {
                case 'entrada':
                    $valor_linea = (float)$linea->valor_cheque;
                    $valor_debito = (float)$linea->valor_cheque;
                    $valor_credito = 0;
                    break;

                case 'salida':
                    $valor_linea = (float)$linea->valor_cheque * -1;
                    $valor_debito = 0;
                    $valor_credito = (float)$linea->valor_cheque;
                    break;
                
                default:
                    throw new \Exception('El motivo seleccionado no define si el cheque es de entrada o salida.');
            }

            $cuenta = null;
            $numero_cheque = isset($linea->numero_cheque) ? trim((string)$linea->numero_cheque) : '';
            $teso_caja_id = isset($linea->caja_id_cheque) ? (int)$linea->caja_id_cheque : 0;
            $teso_cuenta_bancaria_id = 0;
            $fecha_emision = isset($linea->fecha_emision) ? $linea->fecha_emision : $doc_encabezado->fecha;
            $fecha_cobro = isset($linea->fecha_cobro) ? $linea->fecha_cobro : $doc_encabezado->fecha;
            $referencia_cheque = isset($linea->referencia_cheque) ? $linea->referencia_cheque : '';
            $detalle_cheque = isset($linea->detalle_cheque) ? $linea->detalle_cheque : '';

            if ($usarChequera) {
                $medioCheque = TesoMedioRecaudo::find((int)$teso_medio_recaudo_id);
                if (is_null($medioCheque) || !$medioCheque->tiene_cuenta_bancaria_destino((int)$linea->teso_cuenta_bancaria_id_cheque)) {
                    throw new \Exception('La cuenta bancaria seleccionada no está asociada al medio de recaudo Cheque.');
                }

                $emision = $chequePaymentService->emitirDesdeChequera($linea, $doc_encabezado);
                $cuenta = $emision['cuenta'];
                $numero_cheque = (string)$emision['numero'];
                $teso_caja_id = 0;
                $teso_cuenta_bancaria_id = (int)$cuenta->id;
                $fecha_emision = $doc_encabezado->fecha;
                $fecha_cobro = $doc_encabezado->fecha;
                $referencia_cheque = '';
                $detalle_cheque = 'Pago con cheque ' . $numero_cheque;
            }

            $datos = [
                        'fuente' => $fuente,
                        'modalidad' => $fuente === 'propio'
                            ? ($usarChequera ? ChequePaymentService::MODALIDAD_CHEQUERA : ChequePaymentService::MODALIDAD_CREADOS)
                            : null,
                        'tercero_id' => $doc_encabezado->core_tercero_id,
                        'fecha_emision' => $fecha_emision,
                        'fecha_cobro' => $fecha_cobro,
                        'numero_cheque' => $numero_cheque,
                        'referencia_cheque' => $referencia_cheque,
                        'entidad_financiera_id' => $usarChequera ? (int)$cuenta->entidad_financiera_id : (int)$linea->entidad_financiera_id,
                        'valor' => abs( $valor_linea ),
                        'detalle' => $detalle_cheque,
                        'core_tipo_transaccion_id_origen' => $doc_encabezado->core_tipo_transaccion_id,
                        'core_tipo_doc_app_id_origen' => $doc_encabezado->core_tipo_doc_app_id,
                        'consecutivo' => $doc_encabezado->consecutivo,
                        'core_tipo_transaccion_id_consumo' => 0,
                        'core_tipo_doc_app_id_consumo' => 0,
                        'consecutivo_doc_consumo' => 0,
                        'teso_caja_id' => $teso_caja_id,
                        'teso_cuenta_bancaria_id' => $teso_cuenta_bancaria_id,
                        'teso_doc_encabezado_id' => $doc_encabezado->id,
                        'tipo' => $fuente === 'propio' ? 'cheque_propio' : 'cheque_tercero',
                        'creado_por' => Auth::user()->email,
                        'modificado_por' => '',
                        'estado' => $estado
                    ];

            $chequeExistente = ControlCheque::find(isset($linea->cheque_id) ? (int)$linea->cheque_id : 0);
            if ( !$usarChequera && is_null($chequeExistente) )
            {
                ControlCheque::create( $datos );
            } elseif (!$usarChequera) {
                if ($chequeExistente->estado !== 'Recibido') {
                    throw new \Exception('El cheque almacenado ya no está disponible.');
                }
                $chequeExistente->estado = 'Gastado';
                $chequeExistente->modificado_por = Auth::user()->email;
                $chequeExistente->core_tipo_transaccion_id_consumo = $doc_encabezado->core_tipo_transaccion_id;
                $chequeExistente->core_tipo_doc_app_id_consumo = $doc_encabezado->core_tipo_doc_app_id;
                $chequeExistente->consecutivo_doc_consumo = $doc_encabezado->consecutivo;
                $chequeExistente->save();
            }

            $tipo_operacion = $linea->tipo_operacion_id_cheque;
            
            $datos['teso_encabezado_id'] = $doc_encabezado->id;
            $datos['core_tipo_transaccion_id'] = $doc_encabezado->core_tipo_transaccion_id;
            $datos['core_tipo_doc_app_id'] = $doc_encabezado->core_tipo_doc_app_id;
            $datos['core_tercero_id'] = $doc_encabezado->core_tercero_id;
            $datos['core_empresa_id'] = $doc_encabezado->core_empresa_id;
            $datos['codigo_referencia_tercero'] = $doc_encabezado->codigo_referencia_tercero;
            $datos['fecha'] = $doc_encabezado->fecha;
            $datos['teso_motivo_id'] = (int)$linea->teso_motivo_id_cheque;
            $datos['teso_medio_recaudo_id'] = $teso_medio_recaudo_id;
            $datos['detalle_operacion'] = $tipo_operacion;
            $datos['estado'] = 'Activo';
            TesoDocRegistro::create( $datos );
                
            $datos['valor_movimiento'] = $valor_linea;
            $datos['descripcion'] = $tipo_operacion;
            $datos['documento_soporte'] = 'Cheque número ' . $numero_cheque;
            $datos['pdv_id'] = $pdv_id;
            TesoMovimiento::create( $datos );

            // Contabilizar
            $datos['tipo_transaccion'] = '';
            $destino = $usarChequera
                ? $cuenta
                : TesoCaja::find($teso_caja_id);
            if (is_null($destino)) {
                throw new \Exception($usarChequera ? 'Cuenta bancaria no encontrada.' : 'Caja no encontrada.');
            }
            $movimiento_contable = new ContabMovimiento();
            $movimiento_contable->contabilizar_linea_registro( $datos, $destino->contab_cuenta_id, $tipo_operacion, $valor_debito, $valor_credito );

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
