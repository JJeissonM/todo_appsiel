<?php

namespace App\Compras\Services;

use App\Compras\Proveedor;
use App\Http\Controllers\Compras\CompraController;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\Services\AverageCost;
use App\Inventarios\InvMovimiento;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvProducto;
use App\Contabilidad\ContabMovimiento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EntradasPendientesIvaService
{
    protected $costos;

    public function __construct(AverageCost $costos = null)
    {
        $this->costos = $costos ?: new AverageCost();
    }

    // Ejecutar dentro de la transacción que guarda la factura.
    public function preparar(Proveedor $proveedor, array $referencias, $empresaId)
    {
        if (!Schema::hasColumn('inv_doc_encabezados', 'costos_incluyen_iva')) {
            throw new \InvalidArgumentException('Ejecute las migraciones antes de facturar entradas pendientes con IVA incluido.');
        }
        $ids = [];
        foreach ($referencias as $referencia) {
            $id = isset($referencia->id_doc) ? (int)$referencia->id_doc : 0;
            if ($id <= 0 || isset($ids[$id])) {
                throw new \InvalidArgumentException('Seleccione entradas pendientes válidas, sin repetirlas.');
            }
            $ids[$id] = $id;
        }
        if (!$ids) {
            throw new \InvalidArgumentException('Seleccione al menos una entrada pendiente.');
        }
        $entradas = InvDocEncabezado::whereIn('id', array_values($ids))
            ->where('core_empresa_id', $empresaId)
            ->where('core_tercero_id', $proveedor->core_tercero_id)
            ->where('core_tipo_transaccion_id', 35)
            ->where('estado', 'Pendiente')->orderBy('id')->lockForUpdate()->get();
        if ($entradas->count() != count($ids)) {
            throw new \InvalidArgumentException('Todas las entradas deben estar pendientes y pertenecer al proveedor y a la empresa de la factura.');
        }
        foreach ($entradas as $entrada) {
            if ($entrada->costos_incluyen_iva !== null && !(bool)$entrada->costos_incluyen_iva) {
                continue;
            }
            foreach ($entrada->lineas_registros->groupBy('inv_producto_id') as $productoId => $lineas) {
                $datos = CompraController::get_datos_factura_desde_registro_entrada($lineas->first(), $proveedor->id, true);
                if ($datos['tasa_impuesto'] <= 0) {
                    continue;
                }
                $factor = 1 + $datos['tasa_impuesto'] / 100;
                // Ajustar por factor conserva líneas repetidas con distintos costos.
                InvDocRegistro::whereIn('id', $lineas->pluck('id')->all())->update([
                    'costo_unitario'=>DB::raw('costo_unitario / ' . $factor),
                    'costo_total'=>DB::raw('costo_total / ' . $factor),
                ]);
                InvMovimiento::where('core_empresa_id', $empresaId)
                    ->where('inv_doc_encabezado_id', $entrada->id)->where('inv_producto_id', $productoId)
                    ->update([
                        'costo_unitario'=>DB::raw('costo_unitario / ' . $factor),
                        'costo_total'=>DB::raw('costo_total / ' . $factor),
                    ]);
                ContabMovimiento::where([
                    'core_empresa_id'=>$empresaId,
                    'core_tipo_transaccion_id'=>$entrada->core_tipo_transaccion_id,
                    'core_tipo_doc_app_id'=>$entrada->core_tipo_doc_app_id,
                    'consecutivo'=>$entrada->consecutivo,
                    'inv_producto_id'=>$productoId,
                ])->update([
                    'valor_operacion'=>DB::raw('valor_operacion / ' . $factor),
                    'valor_debito'=>DB::raw('valor_debito / ' . $factor),
                    'valor_credito'=>DB::raw('valor_credito / ' . $factor),
                    'valor_saldo'=>DB::raw('valor_saldo / ' . $factor),
                ]);
                $item = InvProducto::findOrFail($productoId);
                if ($item->tipo == 'producto') {
                    foreach ($lineas->groupBy('inv_bodega_id') as $bodegaId => $lineasBodega) {
                        $cantidad = $lineasBodega->sum('cantidad');
                        if ($cantidad == 0) {
                            continue;
                        }
                        $costo = $lineasBodega->sum('costo_total') / $factor / $cantidad;
                        $promedio = $this->costos->calculate_average_cost($bodegaId, $productoId, $costo, $entrada->fecha, $cantidad);
                        $this->costos->set_costo_promedio($bodegaId, $productoId, $promedio);
                    }
                }
            }
            // Evitar descontar IVA otra vez si se anula y se vuelve a facturar.
            $entrada->update(['costos_incluyen_iva'=>0]);
        }
    }
}
