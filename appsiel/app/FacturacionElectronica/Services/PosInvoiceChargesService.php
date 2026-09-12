<?php

namespace App\FacturacionElectronica\Services;

use App\VentasPos\FacturaPos;
use App\VentasPos\Services\TipService;
use App\VentasPos\Services\DatafonoService;
use App\VentasPos\Services\PaymentReconciliationService;

class PosInvoiceChargesService
{
    /** Cargos globales de DATAICO: base_amount es el importe, discount define el signo.
     * https://portaldelcliente.dataico.com/es/knowledge/error-api-response-3
     */
    public function getCharges($invoice)
    {
        $pos = $this->findPosInvoice($invoice);
        $adjustment = round((float)$invoice->valor_ajuste_al_peso, 2);
        if (abs((float)$invoice->valor_total_bolsas) >= 0.01) {
            throw new \UnexpectedValueException('El cobro de bolsas debe representarse con su impuesto en las líneas electrónicas.');
        }
        if (!$pos) {
            if ($adjustment != 0) {
                throw new \UnexpectedValueException('No se encontró la factura POS para verificar el ajuste al peso.');
            }
            return [];
        }

        $tip = round((new TipService())->get_tip_amount($pos), 2);
        $fee = round((new DatafonoService())->get_datafono_amount($pos), 2);
        $charges = [];
        foreach (['PROPINA' => $tip, 'COMISION DATAFONO' => $fee, 'REDONDEO' => $adjustment] as $reason => $amount) {
            if ($amount != 0) {
                $charges[] = ['reason' => $reason, 'base_amount' => abs($amount), 'discount' => $amount < 0];
            }
        }
        if ($charges && config('facturacion_electronica.proveedor_tecnologico_default') !== 'DATAICO') {
            throw new \UnexpectedValueException('Los cargos y redondeos POS requieren un formato de envío implementado para el proveedor electrónico seleccionado.');
        }
        if ($pos->forma_pago === 'contado') {
            $lines = json_decode((string)$pos->lineas_registros_medios_recaudos, true);
            $paid = (new PaymentReconciliationService())->sumar_recaudos(is_array($lines) ? $lines : []);
            $payable = round((float)$invoice->valor_total + $tip + $fee + $adjustment, 2);
            if (round(abs($paid - $payable), 2) > 0.01) {
                throw new \UnexpectedValueException('Los medios de pago no coinciden con productos, propina, comisión y redondeo de la factura electrónica.');
            }
        }
        return $charges;
    }

    protected function findPosInvoice($invoice)
    {
        if (!(int)$invoice->ventas_doc_relacionado_id) {
            return null;
        }
        // El mismo id puede existir en documentos de ventas no relacionados con POS.
        return FacturaPos::where('id', (int)$invoice->ventas_doc_relacionado_id)
            ->where('core_empresa_id', $invoice->core_empresa_id)
            ->where('core_tipo_transaccion_id', $invoice->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id', $invoice->core_tipo_doc_app_id)
            ->where('consecutivo', $invoice->consecutivo)->first();
    }
}
