<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\CxP\CxpMovimiento;
use App\CxP\DocumentosPendientes;
use App\Http\Controllers\Compras\CompraController;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvMotivo;
use App\Tesoreria\TesoMovimiento;
use App\Core\Services\TurnoAssignmentResolver;
use App\Core\Services\TurnoContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraConfirmationService
{
    public function confirm(ComprasDocEncabezado $documento, array $registros_medio_pago = [])
    {
        $operation = function () use ($documento, $registros_medio_pago) {
            return DB::transaction(function () use ($documento, $registros_medio_pago) {
                // Mismo orden de bloqueo que la sincronización: empresa, factura, entradas.
                \App\Core\Empresa::where('id', Auth::user()->empresa_id)->lockForUpdate()->firstOrFail();
                $documento = ComprasDocEncabezado::where('core_empresa_id', Auth::user()->empresa_id)
                    ->lockForUpdate()->findOrFail($documento->id);
                if ($documento->estado === 'Anulado') {
                    throw new \Exception('No es posible confirmar un documento anulado.');
                }
                if ($documento->sincronizado_bot && !empty($documento->cufe)
                    && ComprasDocEncabezado::where('core_empresa_id', $documento->core_empresa_id)
                        ->where('cufe', $documento->cufe)->where('id', '<>', $documento->id)
                        ->where('estado', '<>', 'Anulado')->exists()) {
                    throw new \Exception('Existe otra factura con el mismo CUFE. Revise el duplicado antes de generar una entrada de almacén.');
                }
                if ($this->hasPurchaseMovements($documento) || $this->hasGeneratedFinancialRecords($documento)) {
                    throw new \Exception('El documento ya tiene movimientos o registros financieros. Revise el estado antes de confirmar.');
                }
                if (!$documento->lineas_registros()->where('estado', 'Activo')->exists()) {
                    throw new \Exception('El documento no tiene líneas registradas para confirmar.');
                }
                $documento->load([
                    'lineas_registros' => function ($query) {
                        $query->where('estado', 'Activo')->lockForUpdate();
                    },
                    'proveedor'
                ]);

                if (!$documento->proveedor || (int)$documento->proveedor->core_tercero_id !== (int)$documento->core_tercero_id) {
                    throw new \Exception('La factura debe tener un proveedor válido asociado al mismo tercero.');
                }

                $this->ensureLinesHaveMotives($documento);
                foreach ($documento->lineas_registros as $linea) {
                    if (!(int)$linea->inv_producto_id) {
                        throw new \Exception('La línea ' . $linea->id . ' está pendiente de mapear. '
                            . 'Asocie el ítem del Bot a un producto o servicio antes de confirmar. '
                            . 'Los servicios no generan movimientos de inventario.');
                    }
                    $producto = \App\Inventarios\InvProducto::where('core_empresa_id', $documento->core_empresa_id)
                        ->where('estado', 'Activo')->find($linea->inv_producto_id);
                    if (!$producto) {
                        throw new \Exception('El producto o servicio de la línea ' . $linea->id
                            . ' no está activo o no pertenece a la empresa. Revise su asociación.');
                    }
                    if (!is_finite((float)$linea->cantidad) || (float)$linea->cantidad <= 0) {
                        throw new \Exception('La cantidad de la línea ' . $linea->id . ' debe ser positiva.');
                    }
                    $linea->update($this->getEffectiveLineValues($linea, $documento));
                }
                $neto = round($documento->lineas_registros->sum('precio_total')
                    - $documento->lineas_registros->sum('valor_retencion')
                    - (new ReteicaService())->liquidar_documento($documento), 2);
                if ($documento->forma_pago == 'contado') {
                    $motivo = \App\Tesoreria\TesoMotivo::where('core_empresa_id', $documento->core_empresa_id)
                        ->where('estado', 'Activo')->where('movimiento', 'salida')
                        ->find((int)config('tesoreria.motivo_tesoreria_compras_contado'));
                    if (!$motivo) {
                        throw new \InvalidArgumentException('Configure un motivo activo de compras de contado en tesorería para esta empresa.');
                    }
                    foreach ($registros_medio_pago as &$pago) {
                        $pago['teso_motivo_id'] = $motivo->id;
                    }
                    unset($pago);
                    $registros_medio_pago = (new CompraConfirmationPaymentService())->validate(
                        $registros_medio_pago, $neto, $documento->core_empresa_id
                    );
                } else {
                    $registros_medio_pago = [];
                }

                $request = $this->buildRequestFromDocument($documento);
                $request['registros_medio_pago'] = $registros_medio_pago;
                if (!\App\Inventarios\InvBodega::where('core_empresa_id', $documento->core_empresa_id)
                    ->where('estado', 'Activo')->find($request->inv_bodega_id)) {
                    throw new \Exception('Seleccione una bodega activa de la empresa para la entrada de almacén.');
                }

                if ($documento->forma_pago == 'contado' && empty($registros_medio_pago)) {
                    throw new \Exception('Debe ingresar un medio de pago para confirmar una factura de contado.');
                }

                if (!$this->hasLinkedWarehouseEntryIds($documento)) {
                    $request['entrada_almacen_id'] = app(CompraController::class)->crear_entrada_almacen($request);
                    $documento->entrada_almacen_id = $request['entrada_almacen_id'];
                    $documento->save();
                } else {
                    $this->ensureWarehouseEntriesExist($documento);
                }

                $this->markWarehouseEntriesAsBilled($documento->entrada_almacen_id);
                $this->createPurchaseMovementsAndFinancialRecords($documento, $request->all());

                if ((float)$documento->lineas_registros->sum('valor_retencion') > 0) {
                    (new ContabilidadService())->aplicar_retenciones_por_linea_compras($documento);
                }

                (new ReteicaService())->contabilizar($documento);
                $this->ensureAccountingIsBalanced($documento);
                $where = ['core_empresa_id'=>$documento->core_empresa_id,
                    'core_tipo_transaccion_id'=>$documento->core_tipo_transaccion_id,
                    'core_tipo_doc_app_id'=>$documento->core_tipo_doc_app_id, 'consecutivo'=>$documento->consecutivo];
                $pagado = $documento->forma_pago == 'contado'
                    ? abs((float)TesoMovimiento::where($where)->sum('valor_movimiento'))
                    : (float)CxpMovimiento::where($where)->where('core_tercero_id', $documento->core_tercero_id)->sum('valor_documento');
                if (round($pagado, 2) != $neto) {
                    throw new \Exception('Los movimientos de tesorería o CxP no coinciden con el valor neto a pagar.');
                }
            });
        };

        if (!empty($documento->turno_operativo_id)) {
            $turno = app(TurnoAssignmentResolver::class)->recoverFromOrigin(
                get_class($documento),
                (int)$documento->id
            );

            // Confirmar una compra importada o diferida es procesamiento técnico
            // del hecho original: conserva su turno aunque éste ya haya cerrado.
            return app(TurnoContext::class)->runFromOrigin(
                $turno,
                get_class($documento),
                (int)$documento->id,
                $operation
            );
        }

        return $operation();
    }

    public function getPayableTotal(ComprasDocEncabezado $documento)
    {
        $total = 0;
        $base = 0;
        $retenciones = 0;
        foreach ($documento->lineas_registros()->where('estado', 'Activo')->get() as $linea) {
            $valores = $this->getEffectiveLineValues($linea, $documento);
            $total += $valores['precio_total'];
            $base += $valores['base_impuesto'];
            $retenciones += (float)$linea->valor_retencion;
        }
        $reteica = (float)$documento->reteica_valor;
        if ((int)$documento->reteica_retencion_id) {
            $retencion = \App\Contabilidad\Retencion::where('estado', 'Activo')->find($documento->reteica_retencion_id);
            if ($retencion) {
                $original = ComprasDocEncabezado::where('core_empresa_id', $documento->core_empresa_id)->findOrFail($documento->id);
                $base = $original->reteica_base_manual ? (float)$original->reteica_base : $base;
                $reteica = (new ReteicaService())->calcular($base, $retencion->tasa_retencion);
            }
        }
        return round($total - $retenciones - $reteica, 2);
    }

    protected function buildRequestFromDocument(ComprasDocEncabezado $documento)
    {
        $proveedor = $documento->proveedor;
        if (is_null($proveedor)) {
            throw new \Exception('El documento no tiene un proveedor válido asociado.');
        }

        $inv_bodega_id = (int)$proveedor->inv_bodega_id;
        $entrada_almacen_id = 0;
        if (!empty($documento->entrada_almacen_id)) {
            $entrada_almacen_id = (int)explode(',', $documento->entrada_almacen_id)[0];
        }

        if ($entrada_almacen_id > 0) {
            $entrada_almacen = InvDocEncabezado::find($entrada_almacen_id);
            if (!is_null($entrada_almacen)) {
                $inv_bodega_id = (int)$entrada_almacen->inv_bodega_id;
            }
        }

        if ($inv_bodega_id === 0) {
            $inv_bodega_id = (int)config('inventarios.item_bodega_principal_id');
        }

        $motivo_default_id = $this->getMotiveDefaultId();

        $lineas_registros = $documento->lineas_registros->map(function ($linea) {
            $cantidad = (float)$linea->cantidad;
            $costo_total = (float)$linea->base_impuesto;

            if ($costo_total == 0) {
                $costo_total = (float)$linea->precio_total;
            }

            $costo_unitario = 0;
            if ($cantidad != 0) {
                $costo_unitario = $costo_total / $cantidad;
            }

            return [
                'inv_motivo_id' => (int)$linea->inv_motivo_id,
                'inv_producto_id' => (int)$linea->inv_producto_id,
                'cantidad' => $cantidad,
                'costo_unitario' => $costo_unitario,
                'costo_total' => $costo_total,
                'precio_unitario' => (float)$linea->precio_unitario,
                'precio_total' => (float)$linea->precio_total,
                'base_impuesto' => (float)$linea->base_impuesto,
                'tasa_impuesto' => (float)$linea->tasa_impuesto,
                'valor_impuesto' => (float)$linea->valor_impuesto,
                'tasa_descuento' => (float)$linea->tasa_descuento,
                'valor_total_descuento' => (float)$linea->valor_total_descuento,
                'base_retencion' => $linea->base_retencion,
                'tasa_retencion' => $linea->tasa_retencion,
                'valor_retencion' => $linea->valor_retencion,
                'contab_retencion_id' => (int)$linea->contab_retencion_id
            ];
        })->values()->toArray();

        if ($motivo_default_id > 0) {
            foreach ($lineas_registros as &$lr) {
                if (empty($lr['inv_motivo_id']) || (int)$lr['inv_motivo_id'] <= 0) {
                    $lr['inv_motivo_id'] = $motivo_default_id;
                }
            }
            unset($lr);
        }

        return new Request([
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo,
            'fecha' => $documento->fecha,
            'core_tercero_id' => $documento->core_tercero_id,
            'proveedor_id' => $documento->proveedor_id,
            'comprador_id' => $documento->comprador_id,
            'forma_pago' => $documento->forma_pago,
            'cta_x_pagar_id' => $documento->cta_x_pagar_id,
            'fecha_recepcion' => $documento->fecha_recepcion,
            'fecha_vencimiento' => $documento->fecha_vencimiento,
            'descripcion' => $documento->descripcion,
            'clase_proveedor_id' => (int)$proveedor->clase_proveedor_id,
            'liquida_impuestos' => (int)$proveedor->liquida_impuestos,
            'inv_bodega_id' => $inv_bodega_id,
            'creado_por' => Auth::check() ? Auth::user()->email : $documento->creado_por,
            'estado' => 'Activo',
            'lineas_registros' => json_encode($lineas_registros),
            'registros_medio_pago' => [],
            'turno_operativo_id' => $documento->turno_operativo_id
        ]);
    }

    protected function createPurchaseMovementsAndFinancialRecords(ComprasDocEncabezado $documento, array $datos)
    {
        $datos['descripcion'] = $documento->descripcion;
        $datos['consecutivo'] = $documento->consecutivo;
        $datos['entrada_almacen_id'] = $documento->entrada_almacen_id;
        $datos['clase_proveedor_id'] = $datos['clase_proveedor_id'] ?? ($documento->proveedor ? $documento->proveedor->clase_proveedor_id : null);
        $datos['estado'] = 'Activo';
        $datos['creado_por'] = Auth::user()->email;

        $detalle_operacion = $documento->descripcion;
        $total_documento = 0;
        $total_retenciones = 0;

        foreach ($documento->lineas_registros as $linea) {
            $motive_id = (int)$linea->inv_motivo_id;
            if ($motive_id <= 0) {
                $motive_id = $this->getMotiveDefaultId();
            }

            $valores_linea = $this->getEffectiveLineValues($linea, $documento);
            $linea->update($valores_linea);

            $linea_datos = [
                'compras_doc_encabezado_id' => $documento->id,
                'inv_motivo_id' => $motive_id,
                'inv_bodega_id' => $datos['inv_bodega_id'],
                'inv_producto_id' => $linea->inv_producto_id,
                'precio_unitario' => $valores_linea['precio_unitario'],
                'cantidad' => $linea->cantidad,
                'precio_total' => $valores_linea['precio_total'],
                'base_impuesto' => $valores_linea['base_impuesto'],
                'tasa_impuesto' => $valores_linea['tasa_impuesto'],
                'valor_impuesto' => $valores_linea['valor_impuesto'],
                'tasa_descuento' => $linea->tasa_descuento,
                'valor_total_descuento' => $linea->valor_total_descuento
            ];

            ComprasMovimiento::create($datos + $linea_datos);

            CompraController::contabilizar_movimiento_debito($datos + $linea_datos, $detalle_operacion);

            $total_documento += $this->getAccountingGrossFromLine($linea, $documento);
            $total_retenciones += (float)$linea->valor_retencion;
        }

        $total_documento = round($total_documento, 2);
        $documento->valor_total = $total_documento;
        $documento->save();

        $total_documento -= (new ReteicaService())->liquidar_documento($documento);
        $datos['valor_total_retefuente'] = $total_retenciones;
        if ($total_retenciones != 0) {
            $total_documento -= $total_retenciones;
        }
        $total_documento = round($total_documento, 2);

        if ($documento->forma_pago == 'contado') {
            (new CompraConfirmationPaymentService())->validate($datos['registros_medio_pago'], $total_documento, $documento->core_empresa_id);
        }
        CompraController::contabilizar_movimiento_credito($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
        CompraController::crear_registro_pago($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
    }

    protected function getEffectiveLineValues($linea, ComprasDocEncabezado $documento)
    {
        $base_impuesto = (float)$linea->base_impuesto;
        $precio_total = (float)$linea->precio_total;
        $valor_impuesto = (float)$linea->valor_impuesto;
        $tasa_impuesto = (float)$linea->tasa_impuesto;

        if ($base_impuesto == 0) {
            $base_impuesto = $precio_total;
        }

        $liquida_impuestos = (int)config('configuracion.liquidacion_impuestos');
        if (!is_null($documento->proveedor)) {
            $liquida_impuestos = $liquida_impuestos && (int)$documento->proveedor->liquida_impuestos;
        }

        if (!$liquida_impuestos) {
            $precio_total = $precio_total != 0 ? $precio_total : $base_impuesto;
            $base_impuesto = $precio_total;
            $valor_impuesto = 0;
            $tasa_impuesto = 0;
        }

        $cantidad = (float)$linea->cantidad;
        $precio_unitario = $cantidad != 0 ? $precio_total / $cantidad : 0;

        return [
            'precio_unitario' => $precio_unitario,
            'precio_total' => $precio_total,
            'base_impuesto' => $base_impuesto,
            'tasa_impuesto' => $tasa_impuesto,
            'valor_impuesto' => $valor_impuesto,
        ];
    }

    protected function getAccountingGrossFromLine($linea, ComprasDocEncabezado $documento)
    {
        $valores_linea = $this->getEffectiveLineValues($linea, $documento);
        $valor_contable = (float)$valores_linea['base_impuesto'] + (float)$valores_linea['valor_impuesto'];

        if ($valor_contable == 0) {
            return (float)$linea->precio_total;
        }

        return $valor_contable;
    }

    protected function ensureLinesHaveMotives(ComprasDocEncabezado $documento)
    {
        $motive_default_id = $this->getMotiveDefaultId();
        if ($motive_default_id <= 0) {
            return;
        }

        foreach ($documento->lineas_registros as $linea) {
            if (empty($linea->inv_motivo_id) || (int)$linea->inv_motivo_id <= 0) {
                $linea->inv_motivo_id = $motive_default_id;
                $linea->save();
            }
            if (!InvMotivo::where('core_empresa_id', $documento->core_empresa_id)
                ->where('estado', 'Activo')->where('movimiento', 'entrada')->find($linea->inv_motivo_id)) {
                throw new \Exception('Configure un motivo de entrada activo para las líneas de compra.');
            }
        }
    }

    protected function getMotiveDefaultId()
    {
        // En facturas sincronizadas por BOT, normalmente inv_motivo_id viene en 0.
        // Al crear la Entrada de Almacén o contabilizar la Factura, se requiere un motivo válido
        // para determinar la cuenta contable (evita "Trying to get property 'cta_contrapartida_id' of non-object").
        $motivo_id = (int) InvMotivo::where('core_empresa_id', Auth::user()->empresa_id)
            ->where('core_tipo_transaccion_id', (int) config('compras.ea_tipo_transaccion_id'))
            ->where('estado', 'Activo')
            ->where('movimiento', 'entrada')
            ->orderBy('id')
            ->value('id');

        if ($motivo_id <= 0) {
            // Fallback conservador: cualquier motivo activo del tipo de transacción de EA (35)
            $motivo_id = (int) InvMotivo::where('core_empresa_id', Auth::user()->empresa_id)
                ->where('core_tipo_transaccion_id', (int) config('compras.ea_tipo_transaccion_id'))
                ->where('estado', 'Activo')
                ->orderBy('id')
                ->value('id');
        }

        return $motivo_id;
    }

    protected function hasPurchaseMovements(ComprasDocEncabezado $documento)
    {
        return ComprasMovimiento::where([
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo
        ])->exists();
    }

    protected function hasGeneratedFinancialRecords(ComprasDocEncabezado $documento)
    {
        $where = [
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo
        ];

        return ContabMovimiento::where($where)->exists()
            || CxpMovimiento::where($where)->exists()
            || DocumentosPendientes::where($where)->exists()
            || TesoMovimiento::where($where)->exists();
    }

    protected function hasLinkedWarehouseEntryIds(ComprasDocEncabezado $documento)
    {
        return !empty($this->getWarehouseEntryIds($documento));
    }

    protected function ensureWarehouseEntriesExist(ComprasDocEncabezado $documento)
    {
        $ids = $this->getWarehouseEntryIds($documento);
        $entradas = InvDocEncabezado::whereIn('id', $ids)->where('core_empresa_id', $documento->core_empresa_id)
            ->where('core_tercero_id', $documento->core_tercero_id)
            ->where('core_tipo_transaccion_id', (int)config('compras.ea_tipo_transaccion_id'))
            ->where('estado', '<>', 'Anulado')->lockForUpdate()->get();
        if ($entradas->count() != count($ids)) {
            throw new \Exception('Las entradas vinculadas deben existir y pertenecer al proveedor y a la empresa de la factura.');
        }
        $otraFactura = ComprasDocEncabezado::where('core_empresa_id', $documento->core_empresa_id)
            ->where('id', '<>', $documento->id)->where('estado', '<>', 'Anulado')
            ->where(function ($query) use ($ids) {
                foreach ($ids as $id) {
                    $query->orWhere('entrada_almacen_id', (string)$id)
                        ->orWhere('entrada_almacen_id', 'LIKE', $id . ',%')
                        ->orWhere('entrada_almacen_id', 'LIKE', '%,' . $id)
                        ->orWhere('entrada_almacen_id', 'LIKE', '%,' . $id . ',%');
                }
            })->exists();
        if ($otraFactura) {
            throw new \Exception('Una entrada vinculada ya pertenece a otra factura.');
        }
        $reales = [];
        foreach ($entradas as $entrada) {
            foreach ($entrada->lineas_registros()->where('estado', '<>', 'Anulado')->get() as $linea) {
                $key = $linea->inv_producto_id;
                if (!isset($reales[$key])) $reales[$key] = ['cantidad'=>0, 'base'=>0];
                $reales[$key]['cantidad'] += (float)$linea->cantidad;
                $reales[$key]['base'] += (float)$linea->costo_total;
            }
        }
        $esperados = [];
        foreach ($documento->lineas_registros as $linea) {
            $key = $linea->inv_producto_id;
            if (!isset($esperados[$key])) $esperados[$key] = ['cantidad'=>0, 'base'=>0];
            $esperados[$key]['cantidad'] += (float)$linea->cantidad;
            $esperados[$key]['base'] += (float)$linea->base_impuesto;
        }
        if (count($reales) != count($esperados)) {
            throw new \Exception('Las líneas de las entradas vinculadas no coinciden con la factura.');
        }
        foreach ($esperados as $key=>$valor) {
            if (!isset($reales[$key]) || abs($reales[$key]['cantidad'] - $valor['cantidad']) > 0.000001
                || abs($reales[$key]['base'] - $valor['base']) > 0.01) {
                throw new \Exception('Las cantidades o costos de las entradas vinculadas no coinciden con la factura.');
            }
        }
    }

    protected function getWarehouseEntryIds(ComprasDocEncabezado $documento)
    {
        if (empty($documento->entrada_almacen_id)) {
            return [];
        }

        $ids = [];
        foreach (explode(',', $documento->entrada_almacen_id) as $value) {
            $value = trim($value);
            if (!preg_match('/^[1-9][0-9]*$/', $value) || isset($ids[(int)$value])) {
                throw new \Exception('La referencia a las entradas vinculadas es inválida o está repetida.');
            }
            $ids[(int)$value] = (int)$value;
        }
        return array_values($ids);
    }

    protected function markWarehouseEntriesAsBilled($entrada_almacen_id)
    {
        if (empty($entrada_almacen_id)) {
            return;
        }

        foreach (explode(',', $entrada_almacen_id) as $doc_id) {
            $registro = InvDocEncabezado::find((int)$doc_id);
            if (is_null($registro)) {
                continue;
            }

            $registro->estado = 'Facturada';
            $registro->save();
        }
    }

    protected function ensureAccountingIsBalanced(ComprasDocEncabezado $documento)
    {
        $where = [
            'core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo
        ];

        $debitos = (float) ContabMovimiento::where($where)->sum('valor_debito');
        $creditos = abs((float) ContabMovimiento::where($where)->sum('valor_credito'));

        if (round($debitos, 2) != round($creditos, 2)) {
            throw new \Exception('La contabilización generada no está cuadrada. Débitos: ' . number_format($debitos, 2, '.', '') . ' Créditos: ' . number_format($creditos, 2, '.', '') . '.');
        }
    }
}
