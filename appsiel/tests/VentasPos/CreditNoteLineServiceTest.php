<?php

use App\FacturacionElectronica\Services\CreditNoteLineService;

class CreditNoteLineServiceTest extends PHPUnit_Framework_TestCase
{
    private function line()
    {
        return (object)[
            'cantidad' => 3, 'cantidad_devuelta' => 0, 'precio_unitario' => 40,
            'precio_total' => 107.10, 'base_impuesto_total' => 90,
            'valor_total_descuento' => 11.90, 'tasa_descuento' => 10, 'tasa_impuesto' => 19
        ];
    }
    public function test_reversa_importes_netos_sin_repetir_descuento()
    {
        $values = (new CreditNoteLineService())->calculate($this->line(), 3);
        $this->assertSame(-107.10, $values['precio_total']);
        $this->assertSame(90.0, $values['base_impuesto_total']);
        $this->assertSame(11.90, $values['valor_total_descuento']);
        $this->assertEquals(17.10, $values['valor_impuesto'] * 3, '', 0.000001);
    }
    public function test_parciales_reparten_centavos_y_suman_total_original()
    {
        $line = $this->line();
        $line->precio_total = 100;
        $line->base_impuesto_total = 84.03;
        $total = $base = $discount = 0;
        for ($i = 0; $i < 3; $i++) {
            $line->cantidad_devuelta = $i;
            $values = (new CreditNoteLineService())->calculate($line, 1);
            $total += $values['precio_total'];
            $base += $values['base_impuesto_total'];
            $discount += $values['valor_total_descuento'];
        }
        $this->assertEquals(-100, $total, '', 0.000001);
        $this->assertEquals(84.03, $base, '', 0.000001);
        $this->assertEquals(11.90, $discount, '', 0.000001);
    }
    public function test_cantidad_fraccionaria_respeta_total_persistido()
    {
        $line = $this->line();
        $line->cantidad = 2.92;
        $line->precio_total = $line->base_impuesto_total = 27000;
        $line->tasa_impuesto = $line->tasa_descuento = $line->valor_total_descuento = 0;
        $values = (new CreditNoteLineService())->calculate($line, 2.92);
        $this->assertSame(-27000.0, $values['precio_total']);
        $this->assertEquals(0, $values['valor_impuesto']);
    }
    public function test_rechaza_devolucion_superior_al_saldo()
    {
        $this->setExpectedException('InvalidArgumentException');
        $line = $this->line();
        $line->cantidad_devuelta = 2;
        (new CreditNoteLineService())->calculate($line, 2);
    }
}
