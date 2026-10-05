<?php

use App\FacturacionElectronica\Services\InvoiceTotalsService;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\FacturaPos;
use App\VentasPos\Services\InvoicingService;

class PosInvoiceRoundingTest extends TestCase
{
    public function test_totales_fraccionados_se_conservan_hasta_el_envio()
    {
        foreach ([0, 5, 19] as $rate) {
            foreach ([11500, 27000, 33700] as $total) {
                $line = (new InvoicingService())->normalizar_importes_linea([
                    'precio_total' => $total,
                    'cantidad' => 2.073846,
                    'tasa_impuesto' => $rate,
                    'tasa_descuento' => 0,
                    'base_impuesto_total' => $total + 0.01
                ]);
                $this->assertEquals($total, $line['precio_total']);
                $this->assertEquals($total, round($line['base_impuesto_total'] + $line['valor_impuesto'] * $line['cantidad'], 2));
                $pos = new FacturaPos();
                $pos->valor_total = $total;
                $pos->setRelation('lineas_registros', collect([(object)$line]));
                $electronic = new VtasDocEncabezado();
                $electronic->valor_total = $total;
                $electronic->setRelation('lineas_registros', collect([(object)$line]));
                $service = new InvoiceTotalsService();
                $this->assertTrue($service->validateConversion($pos, $electronic));
                $this->assertTrue($service->validateBeforeSend($electronic));
            }
        }
    }

    public function test_residuo_historico_de_un_centavo_no_cambia_el_total_enviado()
    {
        foreach ([0, 5, 19] as $rate) {
            foreach ([-0.01, 0.01] as $residue) {
                $line = (object)[
                    'precio_total' => 190000, 'base_impuesto_total' => (190000 + $residue) / (1 + $rate / 100),
                    'cantidad' => 2.073846, 'tasa_impuesto' => $rate, 'tasa_descuento' => 10
                ];
                $originalBase = $line->base_impuesto_total;
                $pos = new FacturaPos(); $pos->valor_total = 190000;
                $pos->setRelation('lineas_registros', collect([$line]));
                $service = new InvoiceTotalsService();
                $this->assertTrue($service->validatePosBeforeConversion($pos));
                $values = $service->getProviderLineValues($line, 2);
                $projected = round($values->quantity * round($values->price, $values->decimals) * 0.9 * (1 + $rate / 100), 2);
                $this->assertEquals(190000, $projected);
                $this->assertSame($originalBase, $line->base_impuesto_total);
            }
        }
    }

    public function test_dos_centavos_no_se_tratan_como_residuo()
    {
        $pos = new FacturaPos(); $pos->valor_total = 190000;
        $pos->setRelation('lineas_registros', collect([(object)[
            'precio_total' => 190000, 'base_impuesto_total' => 190000.02, 'tasa_impuesto' => 0
        ]]));
        $this->setExpectedException('UnexpectedValueException');
        (new InvoiceTotalsService())->validatePosBeforeConversion($pos);
    }

    public function test_un_descuadre_real_sigue_bloqueando_la_conversion()
    {
        $pos = new FacturaPos();
        $pos->valor_total = 11500;
        $pos->setRelation('lineas_registros', collect([(object)[
            'precio_total' => 11500, 'base_impuesto_total' => 11501, 'tasa_impuesto' => 0
        ]]));
        $this->setExpectedException('UnexpectedValueException');
        (new InvoiceTotalsService())->validatePosBeforeConversion($pos);
    }
}
