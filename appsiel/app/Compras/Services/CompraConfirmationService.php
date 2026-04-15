<?php

namespace App\Compras\Services;

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasMovimiento;
use App\Contabilidad\ContabMovimiento;
use App\CxP\CxpMovimiento;
use App\CxP\DocumentosPendientes;
use App\Http\Controllers\Compras\CompraController;
use App\Inventarios\InvDocEncabezado;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CompraConfirmationService
{
    public function confirm(ComprasDocEncabezado $documento)
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

        DB::transaction(function () use ($documento) {
            $documento->load([
                'lineas_registros' => function ($query) {
                    $query->where('estado', 'Activo');
                },
                'proveedor'
            ]);

            $request = $this->buildRequestFromDocument($documento);

            if (!$this->hasWarehouseEntry($documento)) {
                $request['entrada_almacen_id'] = app(CompraController::class)->crear_entrada_almacen($request);
                $documento->entrada_almacen_id = $request['entrada_almacen_id'];
                $documento->save();
            }

            $this->markWarehouseEntriesAsBilled($documento->entrada_almacen_id);
            $this->createPurchaseMovementsAndFinancialRecords($documento, $request->all());

            if ((float)$documento->lineas_registros->sum('valor_retencion') > 0) {
                (new ContabilidadService())->aplicar_retenciones_por_linea_compras($documento);
            }
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
        $datos['clase_proveedor_id'] = $datos['clase_proveedor_id'] ?? optional($documento->proveedor)->clase_proveedor_id;
        $datos['estado'] = 'Activo';
        $datos['creado_por'] = Auth::user()->email;

        $detalle_operacion = $documento->descripcion;
        $total_documento = 0;
        $total_retenciones = 0;

        foreach ($documento->lineas_registros as $linea) {
            $linea_datos = [
                'compras_doc_encabezado_id' => $documento->id,
                'inv_motivo_id' => $linea->inv_motivo_id,
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

            $total_documento += (float)$linea->precio_total;
            $total_retenciones += (float)$linea->valor_retencion;
        }

        $documento->valor_total = $total_documento;
        $documento->save();

        $datos['valor_total_retefuente'] = $total_retenciones;
        if ($total_retenciones != 0) {
            $total_documento -= $total_retenciones;
        }

        CompraController::contabilizar_movimiento_credito($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
        CompraController::crear_registro_pago($documento->forma_pago, $datos, $total_documento, $detalle_operacion);
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

    protected function hasWarehouseEntry(ComprasDocEncabezado $documento)
    {
        if (empty($documento->entrada_almacen_id)) {
            return false;
        }

        $entrada_almacen_id = explode(',', $documento->entrada_almacen_id)[0];

        return InvDocEncabezado::find((int)$entrada_almacen_id) != null;
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
}
