<?php

namespace App\VentasPos\Services;

use App\Ventas\Cliente;
use App\VentasPos\FacturaPos;
use Illuminate\Http\Request;

class InvoiceCustomerService
{
    public function normalizeRequest(Request $request)
    {
        $cliente = $this->findCustomer($request->cliente_id);
        $request->merge([
            'cliente_id' => (int)$cliente->id,
            'core_tercero_id' => (int)$cliente->core_tercero_id,
        ]);

        return $cliente;
    }

    public function validateInvoice(FacturaPos $invoice)
    {
        $cliente = $this->findCustomer($invoice->cliente_id);
        if ((int)$invoice->core_tercero_id !== (int)$cliente->core_tercero_id) {
            throw new \InvalidArgumentException('El tercero de la factura POS no corresponde al cliente asociado. Revise el documento antes de convertirlo a factura electronica.');
        }

        return $cliente;
    }

    protected function findCustomer($id)
    {
        $cliente = Cliente::find((int)$id);
        if (is_null($cliente) || empty($cliente->core_tercero_id) || is_null($cliente->tercero)) {
            throw new \InvalidArgumentException('Debe seleccionar un cliente valido con un tercero asociado.');
        }

        return $cliente;
    }
}
