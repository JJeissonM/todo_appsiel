<?php

namespace App\FacturacionElectronica\Services;

use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;

class InvoiceTotalsService
{
    const TOLERANCE = 0.01;
    const MIN_PAYLOAD_DECIMALS = 6;

    /**
     * Obtiene los valores que debe recibir el proveedor a partir de los
     * totales persistidos. La cantidad se redondea primero y el precio se
     * deriva de esa cantidad para que el redondeo no cambie el total.
     */
    public function getProviderLineValues($line, $configuredDecimals = null)
    {
        $decimals = max(
            self::MIN_PAYLOAD_DECIMALS,
            (int)(is_null($configuredDecimals) ? config('facturacion_electronica.cantidadDecimales') : $configuredDecimals)
        );

        $quantity = round(abs((float)$line->cantidad), $decimals);
        if ($quantity <= 0) {
            throw new \UnexpectedValueException('La factura electrónica contiene una línea con cantidad inválida.');
        }

        $taxRate = (float)$line->tasa_impuesto;
        $discountRate = max(0, min(100, (float)$line->tasa_descuento));
        $taxableAmount = abs((float)$line->base_impuesto_total);

        $grossTaxableAmount = $taxableAmount;
        if ($discountRate > 0 && $discountRate < 100) {
            $grossTaxableAmount = $taxableAmount / (1 - ($discountRate / 100));
        }

        $price = $grossTaxableAmount / $quantity;
        $totalDiscount = max($grossTaxableAmount - $taxableAmount, 0);

        return (object)[
            'decimals' => $decimals,
            'quantity' => $quantity,
            'price' => $price,
            'taxable_amount' => $taxableAmount,
            'total_discount' => $totalDiscount,
            'tax_rate' => $taxRate
        ];
    }

    public function validatePosBeforeConversion(FacturaPos $posInvoice)
    {
        $lines = $posInvoice->lineas_registros;
        if ($lines->isEmpty()) {
            throw new \UnexpectedValueException('La factura POS no tiene líneas para convertir.');
        }

        $linesTotal = round((float)$lines->sum('precio_total'), 2);
        $headerTotal = round((float)$posInvoice->valor_total, 2);
        $this->assertSameAmount(
            $headerTotal,
            $linesTotal,
            'La factura POS no se puede convertir porque el total del encabezado no coincide con sus líneas'
        );

        foreach ($lines as $index => $line) {
            $this->validateStoredLine($line, $index + 1);
        }

        return true;
    }

    public function validateConversion(FacturaPos $posInvoice, VtasDocEncabezado $electronicInvoice)
    {
        $this->validatePosBeforeConversion($posInvoice);

        if (!$electronicInvoice->relationLoaded('lineas_registros')) {
            $electronicInvoice->load('lineas_registros');
        }
        $posLines = $posInvoice->lineas_registros->values();
        $electronicLines = $electronicInvoice->lineas_registros->values();

        if ($posLines->count() !== $electronicLines->count()) {
            throw new \UnexpectedValueException('La conversión electrónica no copió todas las líneas de la factura POS.');
        }

        $this->assertSameAmount(
            (float)$posInvoice->valor_total,
            (float)$electronicInvoice->valor_total,
            'El total de la factura electrónica no coincide con la factura POS'
        );

        foreach ($posLines as $index => $posLine) {
            $electronicLine = $electronicLines->get($index);
            $this->assertSameAmount(
                (float)$posLine->precio_total,
                (float)$electronicLine->precio_total,
                'El total de la línea ' . ($index + 1) . ' cambió durante la conversión electrónica'
            );
            $this->assertSameAmount(
                (float)$posLine->base_impuesto_total,
                (float)$electronicLine->base_impuesto_total,
                'La base gravable de la línea ' . ($index + 1) . ' cambió durante la conversión electrónica'
            );
        }

        return true;
    }

    /**
     * Última barrera antes de enviar a DIAN/proveedor.
     */
    public function validateBeforeSend(VtasDocEncabezado $electronicInvoice)
    {
        if (!$electronicInvoice->relationLoaded('lineas_registros')) {
            $electronicInvoice->load('lineas_registros');
        }
        $linesTotal = round((float)$electronicInvoice->lineas_registros->sum('precio_total'), 2);

        $this->assertSameAmount(
            (float)$electronicInvoice->valor_total,
            $linesTotal,
            'La factura electrónica no se envió porque el total del encabezado no coincide con sus líneas'
        );

        $adjustment = round((float)$electronicInvoice->valor_ajuste_al_peso, 2);
        $bags = round((float)$electronicInvoice->valor_total_bolsas, 2);
        if (abs($adjustment) >= self::TOLERANCE || abs($bags) >= self::TOLERANCE) {
            throw new \UnexpectedValueException(
                'La factura electrónica no se envió porque contiene ajuste al peso o cobro de bolsas, ' .
                'pero esos cargos no están representados en las líneas que se reportan a la DIAN.'
            );
        }

        foreach ($electronicInvoice->lineas_registros as $index => $line) {
            $this->validateStoredLine($line, $index + 1);
            $values = $this->getProviderLineValues($line);

            if ((float)$line->tasa_descuento >= 100 || (float)$line->precio_total == 0) {
                continue;
            }

            $quantity = (float)number_format($values->quantity, $values->decimals, '.', '');
            $price = (float)number_format($values->price, $values->decimals, '.', '');
            $projectedBase = round(
                $quantity * $price * (1 - ((float)$line->tasa_descuento / 100)),
                2
            );
            $projectedTotal = round($projectedBase * (1 + ((float)$line->tasa_impuesto / 100)), 2);

            $this->assertSameAmount(
                (float)$line->precio_total,
                $projectedTotal,
                'La línea ' . ($index + 1) . ' cambiaría de total al enviarse al proveedor tecnológico'
            );
        }

        return true;
    }

    protected function validateStoredLine($line, $lineNumber)
    {
        $base = round(abs((float)$line->base_impuesto_total), 2);
        $tax = round($base * ((float)$line->tasa_impuesto / 100), 2);
        $calculatedTotal = round($base + $tax, 2);

        $this->assertSameAmount(
            abs((float)$line->precio_total),
            $calculatedTotal,
            'La línea ' . $lineNumber . ' no cuadra entre base, impuesto y total'
        );
    }

    protected function assertSameAmount($expected, $actual, $message)
    {
        $difference = round(abs((float)$expected - (float)$actual), 2);
        if ($difference >= self::TOLERANCE) {
            throw new \UnexpectedValueException(
                $message . '. Esperado: $' . number_format((float)$expected, 2, ',', '.') .
                '; calculado: $' . number_format((float)$actual, 2, ',', '.') .
                '; diferencia: $' . number_format($difference, 2, ',', '.') . '.'
            );
        }
    }
}
