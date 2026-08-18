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
        $queries = array(
            FacturaPos::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereBetween('fecha', array($dateFrom, $dateTo)),
            VtasDocEncabezado::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereIn('core_tipo_transaccion_id', $salesInvoiceTransactionIds)
                ->whereBetween('fecha', array($dateFrom, $dateTo)),
            CxcMovimiento::where('core_empresa_id', $stay->empresa_id)
                ->where('core_tercero_id', $terceroId)
                ->whereBetween('fecha', array($dateFrom, $dateTo)),
        );

        $hasInvoices = false;
        $hasActiveInvoices = false;
        foreach ($queries as $query) {
            if (!$query->exists()) {
                continue;
            }

            $hasInvoices = true;
            if ((clone $query)->whereRaw('LOWER(TRIM(COALESCE(estado, ""))) <> ?', array('anulado'))->exists()) {
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
}
