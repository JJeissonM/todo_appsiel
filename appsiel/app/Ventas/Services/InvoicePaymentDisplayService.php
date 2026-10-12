<?php

namespace App\Ventas\Services;

use App\Contabilidad\ContabDocEncabezado;
use App\CxC\CxcAbono;
use App\CxC\CxcDocEncabezado;

class InvoicePaymentDisplayService
{
    // Resuelve únicamente la presentación; no modifica abonos ni documentos.
    public function resolve($payments)
    {
        $groups = array();
        foreach ($payments as $payment) {
            $key = implode(':', array($payment->core_empresa_id, $payment->core_tipo_transaccion_id,
                $payment->core_tipo_doc_app_id, $payment->consecutivo, $payment->core_tercero_id));
            $groups[$key][] = $payment;
        }

        $result = collect();
        foreach ($groups as $group) {
            $originals = $this->originalAdvances($group[0]);
            $remaining = array_sum(array_map(function ($payment) { return (float)$payment->abono; }, $group));
            $rows = array();
            foreach ($originals as $original) {
                if ($remaining <= 0) {
                    break;
                }
                $row = clone $original;
                $row->abono = min($remaining, (float)$original->abono);
                $type = $row->tipo_documento_app;
                $row->documento_prefijo_consecutivo = (is_null($type) ? '' : $type->prefijo . ' ') . $row->consecutivo;
                $rows[] = $row;
                $remaining -= (float)$row->abono;
            }
            // Si la trazabilidad está incompleta, conservar la información original.
            foreach (count($rows) > 0 && $remaining <= 0.01 ? $rows : $group as $row) {
                $result->push($row);
            }
        }
        return $result;
    }

    protected function originalAdvances($payment)
    {
        $note = ContabDocEncabezado::where('core_empresa_id', $payment->core_empresa_id)
            ->where('core_tipo_transaccion_id', $payment->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $payment->core_tipo_doc_app_id)
            ->where('consecutivo', $payment->consecutivo)->where('estado', 'Activo')->first();
        if (is_null($note) || (int)$note->core_tercero_id === (int)$payment->core_tercero_id) {
            return array();
        }
        // Identificar el traslado por sus líneas y terceros, sin depender del texto.
        $debits = $note->lineas_registros()->where('tipo_transaccion', 'crear_cxc')
            ->where('core_tercero_id', $note->core_tercero_id)->sum('valor_debito');
        $credits = $note->lineas_registros()->where('tipo_transaccion', 'crear_anticipo_cxc')
            ->where('core_tercero_id', $payment->core_tercero_id)->sum('valor_credito');
        if ($debits <= 0 || abs($debits - $credits) > 0.01 || abs($debits - (float)$note->valor_total) > 0.01) {
            return array();
        }

        $sources = CxcAbono::where('core_empresa_id', $note->core_empresa_id)
            ->where('doc_cxc_transacc_id', $note->core_tipo_transaccion_id)
            ->where('doc_cxc_tipo_doc_id', $note->core_tipo_doc_app_id)
            ->where('doc_cxc_consecutivo', $note->consecutivo)
            ->where('core_tercero_id', $note->core_tercero_id)->orderBy('id')->get();
        $active = array();
        foreach ($sources as $source) {
            if (CxcDocEncabezado::where('core_empresa_id', $source->core_empresa_id)
                ->where('core_tipo_transaccion_id', $source->doc_cruce_transacc_id)
                ->where('core_tipo_doc_app_id', $source->doc_cruce_tipo_doc_id)
                ->where('consecutivo', $source->doc_cruce_consecutivo)
                ->where('core_tercero_id', $source->core_tercero_id)->where('estado', 'Activo')->exists()) {
                $active[] = $source;
            }
        }
        return $active;
    }
}
