<?php

namespace App\Hotel\Services;

use App\CxC\CxcMovimiento;
use App\Hotel\HotelStay;
use App\Hotel\HotelStayGuest;
use App\Sistema\TipoTransaccion;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;

class HotelGuestBillingService
{
    public function invoiceStatus(HotelStay $stay, HotelStayGuest $guest)
    {
        $terceroId = $this->terceroId($guest);
        if ($terceroId <= 0) {
            return array('has_invoices' => false, 'has_active_invoices' => false);
        }

        $dateFrom = substr((string)$stay->check_in_at, 0, 10);
        $dateTo = !empty($stay->check_out_at) ? substr((string)$stay->check_out_at, 0, 10) : date('Y-m-d');
        $salesInvoiceTransactionIds = TipoTransaccion::where('descripcion', 'LIKE', '%Factura%')->lists('id')->toArray();
        $posInvoices = FacturaPos::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereBetween('fecha', array($dateFrom, $dateTo))
                ->get($this->invoiceIdentityFields());
        $salesInvoices = VtasDocEncabezado::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereIn('core_tipo_transaccion_id', $salesInvoiceTransactionIds)
                ->whereBetween('fecha', array($dateFrom, $dateTo))
                ->get($this->invoiceIdentityFields());
        $receivables = CxcMovimiento::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereIn('core_tipo_transaccion_id', $salesInvoiceTransactionIds)
                ->whereBetween('fecha', array($dateFrom, $dateTo))
                ->get($this->invoiceIdentityFields());

        $hasInvoices = !$posInvoices->isEmpty() || !$salesInvoices->isEmpty() || !$receivables->isEmpty();
        $hasActiveInvoices = false;
        $sourceStates = array();

        foreach (array($posInvoices, $salesInvoices) as $invoices) {
            foreach ($invoices as $invoice) {
                $sourceStates[$this->invoiceIdentityKey($invoice)] = $invoice->estado;
                if (!$this->isCancelled($invoice->estado)) {
                    $hasActiveInvoices = true;
                }
            }
        }

        foreach ($receivables as $receivable) {
            $identityKey = $this->invoiceIdentityKey($receivable);

            // El estado del encabezado de la factura es definitivo. Al anular una
            // factura POS puede quedar temporalmente un movimiento de CxC vigente;
            // ese movimiento residual no debe impedir retirar al acompañante.
            if (array_key_exists($identityKey, $sourceStates)) {
                continue;
            }

            $sourceInvoice = $this->findSourceInvoice($receivable, $salesInvoiceTransactionIds);
            if (!is_null($sourceInvoice)) {
                $sourceStates[$identityKey] = $sourceInvoice->estado;
                if (!$this->isCancelled($sourceInvoice->estado)) {
                    $hasActiveInvoices = true;
                }
                continue;
            }

            // Se conserva la validación para movimientos de cartera que no tienen
            // un encabezado disponible en las tablas de facturación.
            if (!$this->isCancelled($receivable->estado)) {
                $hasActiveInvoices = true;
            }
        }

        return array(
            'has_invoices' => $hasInvoices,
            'has_active_invoices' => $hasActiveInvoices,
        );
    }

    private function terceroId(HotelStayGuest $guest)
    {
        $cliente = $guest->cliente;

        return is_null($cliente) ? 0 : (int)$cliente->core_tercero_id;
    }

    private function invoiceIdentityFields()
    {
        return array(
            'core_empresa_id',
            'core_tercero_id',
            'core_tipo_transaccion_id',
            'core_tipo_doc_app_id',
            'consecutivo',
            'estado',
        );
    }

    private function invoiceIdentityKey($document)
    {
        return implode('|', array(
            $document->core_empresa_id,
            $document->core_tercero_id,
            $document->core_tipo_transaccion_id,
            $document->core_tipo_doc_app_id,
            $document->consecutivo,
        ));
    }

    private function findSourceInvoice($receivable, array $salesInvoiceTransactionIds)
    {
        $conditions = array(
            'core_empresa_id' => $receivable->core_empresa_id,
            'core_tercero_id' => $receivable->core_tercero_id,
            'core_tipo_transaccion_id' => $receivable->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $receivable->core_tipo_doc_app_id,
            'consecutivo' => $receivable->consecutivo,
        );

        $sourceInvoice = FacturaPos::where($conditions)->first(array('estado'));
        if (!is_null($sourceInvoice)) {
            return $sourceInvoice;
        }

        if (!in_array((int)$receivable->core_tipo_transaccion_id, array_map('intval', $salesInvoiceTransactionIds), true)) {
            return null;
        }

        return VtasDocEncabezado::where($conditions)->first(array('estado'));
    }

    private function isCancelled($state)
    {
        return strtolower(trim((string)$state)) === 'anulado';
    }
}
