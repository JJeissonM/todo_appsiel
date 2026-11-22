<?php

use App\Compras\Services\NotaAjusteDocSoporteCalculo as Calculo;

class NotaAjusteDocSoporteCalculoTest extends PHPUnit_Framework_TestCase
{
    // DS: 10 tornillos a 1.000 + IVA 19% con retefuente 2.5%, y un servicio de 500 sin IVA ni retención.
    private function lineas($cantidadAjustada = 0, $baseAjustada = 0)
    {
        return [
            ['id' => 1, 'cantidad' => 10, 'base' => 10000, 'iva' => 1900, 'tasa_impuesto' => 19, 'valor_retencion' => 250,
                'cantidad_ajustada' => $cantidadAjustada, 'base_ajustada' => $baseAjustada],
            ['id' => 2, 'cantidad' => 1, 'base' => 500, 'iva' => 0, 'tasa_impuesto' => 0, 'valor_retencion' => 0,
                'cantidad_ajustada' => 0, 'base_ajustada' => 0],
        ];
    }

    public function test_devolucion_parcial_reversa_la_retencion_en_proporcion()
    {
        $lineas = Calculo::calcular(1, $this->lineas(), [1 => 2]);

        $this->assertCount(1, $lineas);
        $this->assertEquals(2, $lineas[0]['cantidad']);
        $this->assertEquals(2000, $lineas[0]['base']);
        $this->assertEquals(380, $lineas[0]['iva']);
        $this->assertEquals(2380, $lineas[0]['total']);
        $this->assertEquals(50, $lineas[0]['retencion']);
    }

    public function test_devolucion_despues_de_una_rebaja_usa_el_precio_vigente()
    {
        // Ya se rebajaron 1.000 de base: quedan 9.000 para 10 unidades.
        $lineas = Calculo::calcular(1, $this->lineas(0, 1000), [1 => 10]);

        $this->assertEquals(9000, $lineas[0]['base']);
        $this->assertEquals(225, $lineas[0]['retencion']);
    }

    public function test_no_permite_devolver_mas_de_lo_disponible()
    {
        $this->setExpectedException('InvalidArgumentException', 'supera la disponible');
        Calculo::calcular(1, $this->lineas(9, 9000), [1 => 2]);
    }

    public function test_anulacion_toma_todo_el_documento()
    {
        $lineas = Calculo::calcular(2, $this->lineas(), []);
        $totales = Calculo::totales($lineas, 100, 10500);

        $this->assertCount(2, $lineas);
        $this->assertEquals(10500, $totales['base']);
        $this->assertEquals(12400, $totales['total']);
        $this->assertEquals(250, $totales['retefuente']);
        $this->assertEquals(100, $totales['reteica']);
        $this->assertEquals(12050, $totales['neto']);
    }

    public function test_anulacion_bloqueada_si_ya_hay_notas()
    {
        $this->setExpectedException('InvalidArgumentException', 'ya tiene notas');
        Calculo::calcular(2, $this->lineas(1, 1000), []);
    }

    public function test_rebaja_por_valor_no_mueve_cantidades()
    {
        $lineas = Calculo::calcular(3, $this->lineas(), [1 => 1000, 2 => 100]);
        $totales = Calculo::totales($lineas, 105, 10500);

        $this->assertEquals(0, $lineas[0]['cantidad']);
        $this->assertEquals(1190, $lineas[0]['total']);
        $this->assertEquals(25, $lineas[0]['retencion']);
        $this->assertEquals(100, $lineas[1]['total']);
        $this->assertEquals(11, $totales['reteica']); // 105 * 1100 / 10500
        $this->assertEquals(1290 - 25 - 11, $totales['neto']);
    }

    public function test_rebaja_no_supera_la_base_disponible()
    {
        $this->setExpectedException('InvalidArgumentException', 'supera el disponible');
        Calculo::calcular(4, $this->lineas(0, 9500), [1 => 600]);
    }

    public function test_nota_vacia_o_concepto_invalido()
    {
        try {
            Calculo::calcular(1, $this->lineas(), []);
            $this->fail('Debió rechazar una nota sin valores');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('no tiene valores', $e->getMessage());
        }
        $this->setExpectedException('InvalidArgumentException', 'entre 1 y 5');
        Calculo::calcular(6, $this->lineas(), [1 => 1]);
    }

    public function test_tope_por_saldo_de_la_cxp()
    {
        Calculo::validar_saldo('contado', 999999, 0); // contado no tiene tope de CxP
        Calculo::validar_saldo('credito', 100, 100);

        try {
            Calculo::validar_saldo('credito', 10, 0);
            $this->fail('Con saldo 0 se bloquea');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('no tiene saldo pendiente', $e->getMessage());
        }

        $this->setExpectedException('InvalidArgumentException', 'supera el saldo pendiente');
        Calculo::validar_saldo('credito', 100.01, 100);
    }
}
