<?php

namespace App\Inventarios\Services;

use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMotivo;
use App\Inventarios\InvProducto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AjustarSaldosBodegaService
{
    /**
     * Agrega lineas faltantes de productos activos a un documento de inventario fisico.
     */
    public function ejecutar($doc_encabezado_id)
    {
        $doc = InvDocEncabezado::findOrFail($doc_encabezado_id);

        $productos_en_documento = InvDocRegistro::where('inv_doc_encabezado_id', $doc->id)
            ->pluck('inv_producto_id')
            ->unique()
            ->values()
            ->all();

        $productos_activos = InvProducto::where('estado', 'Activo')
            ->where('tipo', 'producto')
            ->where('core_empresa_id', $doc->core_empresa_id)
            ->whereIn('id', function ($query) use ($doc) {
                $query->from('inv_movimientos')
                    ->select('inv_producto_id')
                    ->where('core_empresa_id', $doc->core_empresa_id)
                    ->where('inv_bodega_id', $doc->inv_bodega_id)
                    ->whereNotNull('inv_producto_id')
                    ->groupBy('inv_producto_id');
            })
            ->whereNotIn('id', $productos_en_documento)
            ->pluck('id')
            ->values()
            ->all();

        if (empty($productos_activos)) {
            return (object)[
                'agregadas' => 0
            ];
        }

        $motivo_id = $this->resolver_motivo_id($doc);

        $agregadas = 0;
        DB::beginTransaction();
        try {
            foreach ($productos_activos as $producto_id) {
                InvDocRegistro::create([
                    'core_empresa_id' => $doc->core_empresa_id,
                    'inv_doc_encabezado_id' => $doc->id,
                    'inv_motivo_id' => $motivo_id,
                    'inv_bodega_id' => $doc->inv_bodega_id,
                    'inv_producto_id' => $producto_id,
                    'costo_unitario' => 0,
                    'cantidad' => 0,
                    'costo_total' => 0,
                    'core_tercero_id' => $doc->core_tercero_id,
                    'codigo_referencia_tercero' => '',
                    'estado' => 'Activo',
                    'creado_por' => Auth::user()->email
                ]);
                $agregadas++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return (object)[
            'agregadas' => $agregadas
        ];
    }

    private function resolver_motivo_id($doc)
    {
        $motivo_id = InvDocRegistro::where('inv_doc_encabezado_id', $doc->id)->value('inv_motivo_id');
        if (!is_null($motivo_id)) {
            return (int)$motivo_id;
        }

        $motivo = InvMotivo::where('core_tipo_transaccion_id', $doc->core_tipo_transaccion_id)
            ->where('estado', 'Activo')
            ->orderBy('id')
            ->first();

        if (is_null($motivo)) {
            throw new \RuntimeException('No hay motivo de inventario activo para la transaccion del documento.');
        }

        return (int)$motivo->id;
    }
}
