<?php

namespace App\FacturacionElectronica\Services;

class CreditNoteLineService
{
    public function requestedLines($invoiceId, array $data)
    {
        $result = array();
        $seen = array();
        foreach ((array)$data['doc_registros_ids'] as $index => $id) {
            $quantity = isset($data['cantidad_devolver'][$index]) ? (float)$data['cantidad_devolver'][$index] : 0;
            if ($quantity == 0) {
                continue;
            }
            if (!is_finite($quantity) || $quantity < 0 || isset($seen[(int)$id])) {
                throw new \InvalidArgumentException('Revise las cantidades de devolución y las líneas repetidas del formulario.');
            }
            $line = \App\Ventas\VtasDocRegistro::where('id', (int)$id)
                ->where('vtas_doc_encabezado_id', $invoiceId)->lockForUpdate()->first();
            $this->calculate($line, $quantity);
            $result[] = array('line' => $line, 'quantity' => $quantity, 'index' => $index);
            $seen[(int)$id] = true;
        }
        if (empty($result)) {
            throw new \InvalidArgumentException('Debe ingresar al menos una cantidad para devolver.');
        }
        return $result;
    }

    public function matchRequestedLine(array $selection, array &$used, $productId, $quantity)
    {
        foreach ($selection as $item) {
            $line = $item['line'];
            if (!isset($used[$line->id]) && (int)$line->inv_producto_id === (int)$productId
                && abs($item['quantity'] - abs((float)$quantity)) <= 0.000001) {
                $used[$line->id] = true;
                return $line;
            }
        }
        throw new \InvalidArgumentException('No se pudo relacionar la devolución con la línea seleccionada de la factura. Recargue el formulario y revise sus cantidades.');
    }

    public function calculate($line, $quantity)
    {
        if (!$line) {
            throw new \InvalidArgumentException('El producto devuelto no pertenece a la factura.');
        }
        $original = abs((float)$line->cantidad);
        $returned = max(0, (float)$line->cantidad_devuelta);
        $quantity = abs((float)$quantity);
        if ($original <= 0 || $quantity <= 0 || $returned + $quantity > $original + 0.000001) {
            throw new \InvalidArgumentException('La cantidad de la nota crédito supera el saldo pendiente de la línea de factura.' .
                ' Línea ' . (isset($line->id) ? $line->id : '') .
                ': facturada ' . $original . ', devuelta anteriormente ' . $returned .
                ', disponible ' . max(0, $original - $returned) . ', solicitada ' . $quantity . '.');
        }
        // Diferencias acumuladas: la última devolución recoge el centavo residual.
        $portion = function ($amount) use ($original, $returned, $quantity) {
            $amount = abs((float)$amount);
            return round(round($amount * min($original, $returned + $quantity) / $original, 2)
                - round($amount * $returned / $original, 2), 2);
        };
        $total = $portion($line->precio_total);
        $base = $portion($line->base_impuesto_total);
        $discount = $portion($line->valor_total_descuento);
        return [
            'cantidad' => -$quantity,
            'precio_unitario' => ($total + $discount) / $quantity,
            'precio_total' => -$total,
            'base_impuesto' => $base / $quantity,
            'base_impuesto_total' => $base,
            'valor_impuesto' => ($total - $base) / $quantity,
            'tasa_impuesto' => $line->tasa_impuesto,
            'tasa_descuento' => $line->tasa_descuento,
            'valor_total_descuento' => $discount
        ];
    }
}
