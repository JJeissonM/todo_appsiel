<?php

namespace App\VentasPos\Services;

use App\FacturacionElectronica\Factura;
use App\VentasPos\FacturaPos;

class ElectronicInvoicePrintService
{
    /**
     * Obtiene el encabezado de ventas al que pertenece el resultado DIAN.
     *
     * La impresion POS recibe el id de vtas_pos_doc_encabezados, pero los
     * resultados electronicos se guardan contra vtas_doc_encabezados.
     */
    public function resolveElectronicInvoice($printedDocument)
    {
        if (is_null($printedDocument)) {
            return null;
        }

        $electronicTransactionId = (int)config('facturacion_electronica.transaction_type_id_default', 52);
        if ((int)$printedDocument->core_tipo_transaccion_id !== $electronicTransactionId) {
            return null;
        }

        if (!($printedDocument instanceof FacturaPos)) {
            return Factura::find((int)$printedDocument->id);
        }

        $electronicInvoice = Factura::where('ventas_doc_relacionado_id', (int)$printedDocument->id)
            ->where('core_empresa_id', (int)$printedDocument->core_empresa_id)
            ->where('core_tipo_transaccion_id', $electronicTransactionId)
            ->orderBy('id', 'DESC')
            ->first();

        if (!is_null($electronicInvoice)) {
            return $electronicInvoice;
        }

        // Compatibilidad con conversiones antiguas que no guardaron el id POS
        // en ventas_doc_relacionado_id.
        return Factura::where('core_empresa_id', (int)$printedDocument->core_empresa_id)
            ->where('core_tipo_transaccion_id', $electronicTransactionId)
            ->where('core_tipo_doc_app_id', (int)$printedDocument->core_tipo_doc_app_id)
            ->where('consecutivo', $printedDocument->consecutivo)
            ->orderBy('id', 'DESC')
            ->first();
    }
}
