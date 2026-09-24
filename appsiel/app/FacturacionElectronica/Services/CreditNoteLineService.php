<?php

namespace App\FacturacionElectronica\Services;

class CreditNoteLineService
{
    public function calculate($line, $quantity)
    {
        if (!$line) {
            throw new \InvalidArgumentException('El producto devuelto no pertenece a la factura.');
        }
        $original = abs((float)$line->cantidad);
        $returned = max(0, (float)$line->cantidad_devuelta);
        $quantity = abs((float)$quantity);
        if ($original <= 0 || $quantity <= 0 || $returned + $quantity > $original + 0.000001) {
            throw new \InvalidArgumentException('La cantidad de la nota crédito supera el saldo pendiente de la línea de factura.');
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
