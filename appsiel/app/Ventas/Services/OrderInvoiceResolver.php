<?php

namespace App\Ventas\Services;

use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use Illuminate\Support\Facades\Schema;

class OrderInvoiceResolver
{
    public function resolve($order)
    {
        $id = (int)$order->ventas_doc_relacionado_id;
        if (!$id) { return null; }
        $standard = VtasDocEncabezado::find($id);
        $standard = $this->valid($order, $standard) ? $standard : null;
        $pos = Schema::hasTable('vtas_pos_doc_encabezados') ? FacturaPos::find($id) : null;
        $pos = $this->valid($order, $pos) ? $pos : null;
        if (!$pos) { return $standard; }

        // Los IDs de POS y ventas pertenecen a tablas independientes.
        // Resolver la conversión por identidad contable, también para datos históricos
        // donde ventas_doc_relacionado_id de la electrónica quedó en cero.
        $converted = null;
        if ((int)$pos->core_tipo_transaccion_id !== 47) {
            $converted = VtasDocEncabezado::where('core_empresa_id', $pos->core_empresa_id)
                ->where('core_tipo_transaccion_id', $pos->core_tipo_transaccion_id)
                ->where('core_tipo_doc_app_id', $pos->core_tipo_doc_app_id)
                ->where('consecutivo', $pos->consecutivo)->first();
            if (!$this->valid($order, $converted)) { $converted = null; }
        }
        // Un enlace ambiguo no debe presentar una factura arbitraria.
        if ($standard && (!$converted || (int)$standard->id !== (int)$converted->id)) { return null; }
        return $converted ?: $pos;
    }

    private function valid($order, $invoice)
    {
        if (!$invoice || (int)$invoice->core_empresa_id !== (int)$order->core_empresa_id ||
            !in_array((int)$invoice->core_tipo_transaccion_id, [23, 47, 52, 55])) { return false; }
        // Comparar creación: la fecha operativa puede corresponder a un turno anterior.
        if ($order->created_at && $invoice->created_at && $invoice->created_at < $order->created_at) { return false; }
        return true;
    }
}
