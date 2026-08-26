<?php

namespace App\Nomina\Services;

use App\Contabilidad\ContabMovimiento;
use App\Core\ConsecutivoDocumento;
use App\Core\Tercero;
use App\Core\TipoDocApp;
use App\CxP\CxpAbono;
use App\CxP\CxpMovimiento;
use App\CxP\Services\CxpAccountingAccountResolver;
use App\Nomina\NomDocEncabezado;
use App\Nomina\NomPagoAutomatico;
use App\Nomina\NomPagoAutomaticoDetalle;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMedioRecaudoDestino;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PagoAutomaticoNominaService
{
    const TRANSACCION_PAGO_CXP = 33;

    public function empleadosParaPrevisualizar(NomDocEncabezado $documento)
    {
        $movimientos = $this->queryMovimientosDocumento($documento)
            ->get()
            ->groupBy('core_tercero_id');

        $lineas = [];
        $tercerosAgregados = [];
        foreach ($documento->empleados()->with('tercero')->get() as $contrato) {
            if (is_null($contrato->tercero)) {
                continue;
            }
            if (isset($tercerosAgregados[$contrato->core_tercero_id])) {
                continue;
            }
            $tercerosAgregados[$contrato->core_tercero_id] = true;

            $cxpEmpleado = $movimientos->get($contrato->core_tercero_id, collect());
            $valorOriginal = (float) $cxpEmpleado->sum('valor_documento');
            $saldo = (float) $cxpEmpleado->sum('saldo_pendiente');

            if ($saldo > 0) {
                $estado = 'Disponible';
            } elseif ($valorOriginal > 0) {
                $estado = 'Pagado';
            } else {
                $estado = 'Sin CxP';
            }
            
            $lineas[] = (object) [
                'nom_contrato_id' => (int) $contrato->id,
                'core_tercero_id' => (int) $contrato->core_tercero_id,
                'numero_identificacion' => $contrato->tercero->numero_identificacion,
                'empleado' => $contrato->tercero->descripcion,
                'cuenta_bancaria' => $contrato->tercero->ultima_cuenta_bancaria_activa()!=null ? $contrato->tercero->ultima_cuenta_bancaria_activa()->numero_cuenta : null,
                'valor_documento' => $valorOriginal,
                'saldo_pendiente' => $saldo,
                'estado_pago' => $estado,
                'seleccionable' => $saldo > 0
            ];
        }

        usort($lineas, function ($a, $b) {
            return strcasecmp($a->empleado, $b->empleado);
        });

        return $lineas;
    }

    public function generar($documentoId, array $tercerosSeleccionados, $fechaPago, $medioId, $destinoId, $token)
    {
        $empresaId = (int) Auth::user()->empresa_id;
        $email = Auth::user()->email;
        $tercerosSeleccionados = array_values(array_unique(array_map('intval', $tercerosSeleccionados)));

        return DB::transaction(function () use (
            $documentoId,
            $tercerosSeleccionados,
            $fechaPago,
            $medioId,
            $destinoId,
            $token,
            $empresaId,
            $email
        ) {
            $anterior = NomPagoAutomatico::where('token_solicitud', $token)->lockForUpdate()->first();
            if (!is_null($anterior)) {
                return $anterior;
            }

            $documento = NomDocEncabezado::where('id', (int) $documentoId)
                ->where('core_empresa_id', $empresaId)
                ->lockForUpdate()
                ->first();

            $this->validarDocumento($documento, $fechaPago);
            if (empty($tercerosSeleccionados)) {
                throw new \InvalidArgumentException('Debe seleccionar al menos un empleado con saldo pendiente.');
            }

            $tercerosDocumento = $documento->empleados()
                ->whereIn('nom_contratos.core_tercero_id', $tercerosSeleccionados)
                ->pluck('nom_contratos.core_tercero_id')
                ->map(function ($id) { return (int) $id; })
                ->unique()
                ->values()
                ->toArray();

            sort($tercerosDocumento);
            $seleccionadosOrdenados = $tercerosSeleccionados;
            sort($seleccionadosOrdenados);
            if ($tercerosDocumento !== $seleccionadosOrdenados) {
                throw new \InvalidArgumentException('La selección contiene empleados que no pertenecen al documento de nómina.');
            }

            $destino = $this->validarMedioYDestino($medioId, $destinoId, $empresaId);
            $motivo = $this->resolverMotivo($empresaId);
            $tipoDocumento = $this->resolverTipoDocumentoPago();

            $movimientos = $this->queryMovimientosDocumento($documento)
                ->whereIn('core_tercero_id', $tercerosSeleccionados)
                ->where('saldo_pendiente', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($movimientos->isEmpty()) {
                throw new \InvalidArgumentException('Los empleados seleccionados ya no tienen saldos de CxP disponibles. Actualice la previsualización.');
            }

            $tercerosConSaldo = $movimientos->pluck('core_tercero_id')->map(function ($id) {
                return (int) $id;
            })->unique()->values()->toArray();
            sort($tercerosConSaldo);
            if ($tercerosConSaldo !== $seleccionadosOrdenados) {
                throw new \InvalidArgumentException('Uno o más empleados ya no tienen saldo pendiente. Actualice la previsualización antes de pagar.');
            }

            $total = (float) $movimientos->sum('saldo_pendiente');
            if ($total <= 0) {
                throw new \InvalidArgumentException('El valor total del pago debe ser mayor a cero.');
            }

            $terceroEncabezado = (int) config('nomina.tercero_id_salarios_por_pagar');
            if ($terceroEncabezado <= 0) {
                $terceroEncabezado = (int) $tercerosSeleccionados[0];
            } elseif (!Tercero::where('id', $terceroEncabezado)->where('core_empresa_id', $empresaId)->exists()) {
                throw new \InvalidArgumentException('El tercero configurado para salarios por pagar no pertenece a la empresa actual.');
            }

            $pago = $this->crearEncabezadoPago(
                $documento,
                $tipoDocumento,
                $fechaPago,
                (int) $medioId,
                $destino,
                $terceroEncabezado,
                $total,
                $email
            );

            $auditoria = NomPagoAutomatico::create([
                'core_empresa_id' => $empresaId,
                'nom_doc_encabezado_id' => $documento->id,
                'teso_doc_encabezado_id' => $pago->id,
                'teso_medio_recaudo_id' => (int) $medioId,
                'teso_caja_id' => $destino->teso_caja_id,
                'teso_cuenta_bancaria_id' => $destino->teso_cuenta_bancaria_id,
                'fecha_pago' => $fechaPago,
                'valor_total' => $total,
                'cantidad_empleados' => count($tercerosSeleccionados),
                'estado' => 'Generado',
                'token_solicitud' => $token,
                'creado_por' => $email,
                'modificado_por' => ''
            ]);

            foreach ($movimientos as $movimiento) {
                $this->aplicarMovimientoCxp($pago, $documento, $auditoria, $movimiento, $email);
            }

            $this->crearSalidaTesoreria($pago, $motivo, $destino, $total, $email);

            return $auditoria;
        });
    }

    protected function validarDocumento($documento, $fechaPago)
    {
        if (is_null($documento)) {
            throw new \InvalidArgumentException('El documento de nómina no existe o no pertenece a la empresa actual.');
        }
        if ($documento->estado !== NomDocEncabezado::ESTADO_CONTABILIZADO) {
            throw new \InvalidArgumentException('El documento de nómina debe estar contabilizado antes de generar pagos.');
        }
        if (!$this->fechaValida($fechaPago)) {
            throw new \InvalidArgumentException('La fecha de pago no es válida.');
        }
        if ($fechaPago < $documento->fecha) {
            throw new \InvalidArgumentException('La fecha de pago no puede ser anterior a la fecha del documento de nómina.');
        }
        if ($fechaPago > date('Y-m-d')) {
            throw new \InvalidArgumentException('La fecha de pago no puede ser futura.');
        }
    }

    protected function fechaValida($fecha)
    {
        $objeto = \DateTime::createFromFormat('Y-m-d', (string) $fecha);
        return $objeto && $objeto->format('Y-m-d') === $fecha;
    }

    protected function queryMovimientosDocumento(NomDocEncabezado $documento)
    {
        return CxpMovimiento::where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_tipo_transaccion_id', $documento->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $documento->core_tipo_doc_app_id)
            ->where('consecutivo', $documento->consecutivo);
    }

    protected function validarMedioYDestino($medioId, $destinoId, $empresaId)
    {
        $medio = TesoMedioRecaudo::where('id', (int) $medioId)->where('estado', 'Activo')->first();
        if (is_null($medio)) {
            throw new \InvalidArgumentException('El medio de pago seleccionado no está activo.');
        }

        $destino = TesoMedioRecaudoDestino::where('id', (int) $destinoId)
            ->where('teso_medio_recaudo_id', $medio->id)
            ->where('estado', 'Activo')
            ->first();
        if (is_null($destino)) {
            throw new \InvalidArgumentException('El origen asociado al medio de pago no es válido.');
        }

        if (!is_null($destino->teso_caja_id)) {
            $cajasPermitidas = TesoCaja::get_cajas_permitidas()->pluck('id')->toArray();
            $caja = TesoCaja::where('id', $destino->teso_caja_id)
                ->where('core_empresa_id', $empresaId)
                ->where('estado', 'Activo')
                ->whereIn('id', $cajasPermitidas)
                ->first();
            if (is_null($caja) || (int) $caja->contab_cuenta_id <= 0) {
                throw new \InvalidArgumentException('La caja asociada no está activa o no tiene cuenta contable.');
            }
        } elseif (!is_null($destino->teso_cuenta_bancaria_id)) {
            $cuentasPermitidas = TesoCuentaBancaria::get_cuentas_permitidas()->pluck('id')->toArray();
            $cuenta = TesoCuentaBancaria::where('id', $destino->teso_cuenta_bancaria_id)
                ->where('core_empresa_id', $empresaId)
                ->where('estado', 'Activo')
                ->whereIn('id', $cuentasPermitidas)
                ->first();
            if (is_null($cuenta) || (int) $cuenta->contab_cuenta_id <= 0) {
                throw new \InvalidArgumentException('La cuenta bancaria asociada no está activa o no tiene cuenta contable.');
            }
        } else {
            throw new \InvalidArgumentException('El medio de pago no tiene una caja o cuenta bancaria asociada.');
        }

        return $destino;
    }

    protected function resolverMotivo($empresaId)
    {
        $motivoId = (int) config('nomina.pago_cxp_motivo_id');
        if ($motivoId <= 0) {
            $motivoId = (int) config('tesoreria.motivo_comprobante_egresos_id');
        }
        $motivo = TesoMotivo::where('id', $motivoId)
            ->where('core_empresa_id', $empresaId)
            ->where('estado', 'Activo')
            ->where('movimiento', 'salida')
            ->first();
        if (is_null($motivo)) {
            throw new \InvalidArgumentException('Configure un motivo de Tesorería activo, de salida, para pagos automáticos de nómina.');
        }
        return $motivo;
    }

    protected function resolverTipoDocumentoPago()
    {
        $transaccion = TipoTransaccion::find(self::TRANSACCION_PAGO_CXP);
        if (is_null($transaccion) || $transaccion->estado !== 'Activo') {
            throw new \InvalidArgumentException('La transacción Pagos de CxP (33) no está activa.');
        }
        if ((int) $transaccion->core_modelo_id <= 0) {
            throw new \InvalidArgumentException('La transacción Pagos de CxP (33) no tiene un modelo asociado.');
        }

        $tipoId = (int) config('nomina.pago_cxp_tipo_doc_app_id');
        $query = $transaccion->tipos_documentos()->where('core_tipos_docs_apps.estado', 'Activo');
        $tipo = $tipoId > 0 ? $query->where('core_tipos_docs_apps.id', $tipoId)->first() : $query->first();
        if (is_null($tipo)) {
            throw new \InvalidArgumentException('Configure un tipo de documento activo para la transacción Pagos de CxP.');
        }
        return $tipo;
    }

    protected function crearEncabezadoPago($documento, TipoDocApp $tipoDocumento, $fecha, $medioId, $destino, $terceroId, $total, $email)
    {
        $consecutivo = ConsecutivoDocumento::where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_documento_app_id', $tipoDocumento->id)
            ->lockForUpdate()
            ->first();

        if (is_null($consecutivo)) {
            $consecutivo = ConsecutivoDocumento::create([
                'core_empresa_id' => $documento->core_empresa_id,
                'core_documento_app_id' => $tipoDocumento->id,
                'consecutivo_actual' => 0
            ]);
        }
        $numero = (int) $consecutivo->consecutivo_actual + 1;
        $consecutivo->consecutivo_actual = $numero;
        $consecutivo->save();

        return TesoDocEncabezado::create([
            'core_tipo_transaccion_id' => self::TRANSACCION_PAGO_CXP,
            'core_tipo_doc_app_id' => $tipoDocumento->id,
            'consecutivo' => $numero,
            'fecha' => $fecha,
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tercero_id' => $terceroId,
            'teso_tipo_motivo' => 'pago-proveedores',
            'documento_soporte' => $documento->get_label_documento(),
            'descripcion' => 'Pago automático de nómina - ' . $documento->descripcion,
            'teso_medio_recaudo_id' => $medioId,
            'teso_caja_id' => $destino->teso_caja_id ?: 0,
            'teso_cuenta_bancaria_id' => $destino->teso_cuenta_bancaria_id ?: 0,
            'valor_total' => $total,
            'estado' => 'Activo',
            'creado_por' => $email,
            'modificado_por' => ''
        ]);
    }

    protected function aplicarMovimientoCxp($pago, $documento, $auditoria, CxpMovimiento $movimiento, $email)
    {
        $valor = (float) $movimiento->saldo_pendiente;
        $abono = CxpAbono::create([
            'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
            'consecutivo' => $pago->consecutivo,
            'core_empresa_id' => $pago->core_empresa_id,
            'core_tercero_id' => $movimiento->core_tercero_id,
            'modelo_referencia_tercero_index' => $movimiento->modelo_referencia_tercero_index,
            'referencia_tercero_id' => $movimiento->referencia_tercero_id,
            'fecha' => $pago->fecha,
            'doc_cxp_transacc_id' => $movimiento->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id' => $movimiento->core_tipo_doc_app_id,
            'doc_cxp_consecutivo' => $movimiento->consecutivo,
            'abono' => $valor,
            'creado_por' => $email
        ]);

        $cuentaCxp = (new CxpAccountingAccountResolver())->getPayableAccountId($movimiento);
        if (is_null($cuentaCxp)) {
            $cuentaCxp = (int) config('configuracion.cta_por_pagar_default');
        }
        if ((int) $cuentaCxp <= 0) {
            throw new \InvalidArgumentException('No se pudo determinar la cuenta contable de CxP para uno de los empleados.');
        }

        ContabMovimiento::create($this->datosContables($pago, $movimiento->core_tercero_id, $email) + [
            'contab_cuenta_id' => $cuentaCxp,
            'detalle_operacion' => 'Pago automático nómina: ' . $documento->get_label_documento(),
            'valor_debito' => $valor,
            'valor_credito' => 0,
            'valor_saldo' => $valor
        ]);

        $movimiento->actualizar_saldos($valor);

        $contratoId = $documento->empleados()
            ->where('nom_contratos.core_tercero_id', $movimiento->core_tercero_id)
            ->value('nom_contratos.id');

        NomPagoAutomaticoDetalle::create([
            'nom_pago_automatico_id' => $auditoria->id,
            'nom_contrato_id' => $contratoId,
            'core_tercero_id' => $movimiento->core_tercero_id,
            'cxp_movimiento_id' => $movimiento->id,
            'cxp_abono_id' => $abono->id,
            'valor_pagado' => $valor
        ]);
    }

    protected function crearSalidaTesoreria($pago, $motivo, $destino, $total, $email)
    {
        $datos = [
            'teso_encabezado_id' => $pago->id,
            'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
            'consecutivo' => $pago->consecutivo,
            'fecha' => $pago->fecha,
            'core_empresa_id' => $pago->core_empresa_id,
            'core_tercero_id' => $pago->core_tercero_id,
            'teso_medio_recaudo_id' => $pago->teso_medio_recaudo_id,
            'teso_motivo_id' => $motivo->id,
            'teso_caja_id' => $destino->teso_caja_id ?: 0,
            'teso_cuenta_bancaria_id' => $destino->teso_cuenta_bancaria_id ?: 0,
            'detalle_operacion' => 'pago-proveedores',
            'descripcion' => 'Pago automático de nómina',
            'documento_soporte' => $pago->documento_soporte,
            'valor' => $total,
            'estado' => 'Activo',
            'creado_por' => $email,
            'modificado_por' => ''
        ];

        TesoDocRegistro::create($datos);
        TesoMovimiento::create($datos + ['valor_movimiento' => $total * -1]);

        if (!is_null($destino->teso_caja_id)) {
            $recurso = TesoCaja::find($destino->teso_caja_id);
        } else {
            $recurso = TesoCuentaBancaria::find($destino->teso_cuenta_bancaria_id);
        }

        ContabMovimiento::create($this->datosContables($pago, $pago->core_tercero_id, $email) + [
            'contab_cuenta_id' => $recurso->contab_cuenta_id,
            'detalle_operacion' => 'Salida pago automático de nómina',
            'valor_debito' => 0,
            'valor_credito' => $total * -1,
            'valor_saldo' => $total * -1,
            'teso_caja_id' => $destino->teso_caja_id ?: 0,
            'teso_cuenta_bancaria_id' => $destino->teso_cuenta_bancaria_id ?: 0
        ]);
    }

    protected function datosContables($pago, $terceroId, $email)
    {
        return [
            'core_tipo_transaccion_id' => $pago->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $pago->core_tipo_doc_app_id,
            'consecutivo' => $pago->consecutivo,
            'fecha' => $pago->fecha,
            'core_empresa_id' => $pago->core_empresa_id,
            'core_tercero_id' => $terceroId,
            'documento_soporte' => $pago->documento_soporte,
            'estado' => 'Activo',
            'creado_por' => $email,
            'modificado_por' => ''
        ];
    }
}
