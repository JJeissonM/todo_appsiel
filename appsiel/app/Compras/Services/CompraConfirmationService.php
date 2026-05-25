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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraConfirmationService
{
    public function confirm(ComprasDocEncabezado $documento, array $registros_medio_pago = [])
    {
        if ($documento->estado === 'Anulado') {
            throw new \Exception('No es posible confirmar un documento anulado.');
        }

        if ($this->hasPurchaseMovements($documento)) {
            throw new \Exception('El documento ya tiene movimientos de compras registrados.');
        }

        if (!$documento->lineas_registros()->where('estado', 'Activo')->exists()) {
            throw new \Exception('El documento no tiene líneas registradas para confirmar.');
        }

        if ($this->hasGeneratedFinancialRecords($documento)) {
            throw new \Exception('El documento ya tiene contabilizaciones o registros financieros generados. Revise el estado antes de confirmar.');
        }

        DB::transaction(function () use ($documento, $registros_medio_pago) {
            $documento->load([
                'lineas_registros' => function ($query) {
                    $query->where('estado', 'Activo');
                },
                'proveedor'
            ]);

            $this->ensureLinesHaveMotives($documento);

            $request = $this->buildRequestFromDocument($documento);
            $request['registros_medio_pago'] = $registros_medio_pago;

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

            $this->ensureAccountingIsBalanced($documento);
        });
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
                'tasa_descuento' => (float)$linea->tasa_descuento,
                'valor_total_descuento' => (float)$linea->valor_total_descuento,
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
            'fecha_recepcion' => $documento->fecha_recepcion,
            'fecha_vencimiento' => $documento->fecha_vencimiento,
            'descripcion' => $documento->descripcion,
            'clase_proveedor_id' => (int)$proveedor->clase_proveedor_id,
            'liquida_impuestos' => (int)$proveedor->liquida_impuestos,
            'inv_bodega_id' => $inv_bodega_id,
            'creado_por' => Auth::user()->email,
            'estado' => 'Activo',
            'lineas_registros' => json_encode($lineas_registros),
            'registros_medio_pago' => []
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

            $linea_datos = [
                'compras_doc_encabezado_id' => $documento->id,
                'inv_motivo_id' => $motive_id,
                'inv_bodega_id' => $datos['inv_bodega_id'],
                'inv_producto_id' => $linea->inv_producto_id,
                'precio_unitario' => $linea->precio_unitario,
                'cantidad' => $linea->cantidad,
                'precio_total' => $linea->precio_total,
                'base_impuesto' => $linea->base_impuesto,
                'tasa_impuesto' => $linea->tasa_impuesto,
                'valor_impuesto' => $linea->valor_impuesto,
                'tasa_descuento' => $linea->tasa_descuento,
                'valor_total_descuento' => $linea->valor_total_descuento
            ];

            ComprasMovimiento::create($datos + $linea_datos);

            CompraController::contabilizar_movimiento_debito($datos + $linea_datos, $detalle_operacion);

            $total_documento += $this->getAccountingGrossFromLine($linea);
            $total_retenciones += (float)$linea->valor_retencion;
        }

        $total_documento = round($total_documento, 2);
        $documento->valor_total = $total_documento;
        $documento->save();

        $datos['valor_total_retefuente'] = $total_retenciones;
        if ($total_retenciones != 0) {
            $total_documento -= $total_retenciones;
        }
        $total_documento = round($total_documento, 2);

        CompraController::contabilizar_movimiento_credito($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
        CompraController::crear_registro_pago($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
    }

    protected function getAccountingGrossFromLine($linea)
    {
        $valor_contable = (float)$linea->base_impuesto + (float)$linea->valor_impuesto;

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
        foreach ($ids as $entrada_almacen_id) {
            if (InvDocEncabezado::find($entrada_almacen_id) == null) {
                throw new \Exception('La factura tiene una entrada de almacén vinculada que no existe: ' . $entrada_almacen_id . '.');
            }
        }
    }

    protected function getWarehouseEntryIds(ComprasDocEncabezado $documento)
    {
        if (empty($documento->entrada_almacen_id)) {
            return [];
        }

        return array_values(array_filter(
            array_map('intval', explode(',', $documento->entrada_almacen_id)),
            function ($id) {
                return $id > 0;
            }
        ));
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
