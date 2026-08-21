<?php

use App\Nomina\Services\RetencionFuenteLaboralService;

class RetencionFuenteLaboralServiceTest extends TestCase
{
    /** @test */
    public function no_retiene_cuando_la_base_gravable_no_supera_95_uvt()
    {
        $resultado = $this->servicio()->calcular([
            'uvt' => 52374,
            'total_pagos' => 95 * 52374,
        ]);

        $this->assertSame(0.0, $resultado['valor_retencion']);
    }

    /** @test */
    public function aplica_el_procedimiento_uno_y_la_tabla_del_articulo_383()
    {
        $resultado = $this->servicio()->calcular([
            'uvt' => 52374,
            'total_pagos' => 10000000,
        ]);

        $baseEsperada = 7500000;
        $retencionEsperada = round(((($baseEsperada / 52374) - 95) * 0.19) * 52374, 0);

        $this->assertEquals($baseEsperada, $resultado['base_retencion']);
        $this->assertEquals($retencionEsperada, $resultado['valor_retencion']);
        $this->assertSame(2, $resultado['rango']->fila_rango);
    }

    /** @test */
    public function limita_las_deducciones_particulares_y_el_total_al_cuarenta_por_ciento()
    {
        $resultado = $this->servicio()->calcular([
            'uvt' => 52374,
            'total_pagos' => 20000000,
            'intereses_vivienda' => 9000000,
            'salud_prepagada' => 2000000,
            'aplica_dependientes' => true,
        ]);

        $this->assertEquals(100 * 52374, $resultado['intereses_vivienda']);
        $this->assertEquals(16 * 52374, $resultado['salud_prepagada']);
        $this->assertEquals(32 * 52374, $resultado['deduccion_por_dependientes']);
        $this->assertEquals(8000000, $resultado['deducciones_rentas_exentas_aplicadas']);
        $this->assertEquals(12000000, $resultado['base_retencion']);
    }

    /** @test */
    public function respeta_el_limite_anual_de_790_uvt_para_la_renta_exenta_del_25_por_ciento()
    {
        $uvt = 52374;
        $resultado = $this->servicio()->calcular([
            'uvt' => $uvt,
            'total_pagos' => 10000000,
            'renta_exenta_25_acumulada' => (790 * $uvt) - 100000,
        ]);

        $this->assertEquals(100000, $resultado['renta_trabajo_exenta']);
    }

    /** @test */
    public function limita_pension_voluntaria_y_afc_al_treinta_por_ciento_y_3800_uvt_anuales()
    {
        $uvt = 52374;
        $resultado = $this->servicio()->calcular([
            'uvt' => $uvt,
            'total_pagos' => 10000000,
            'aportes_pension_voluntaria' => 3000000,
            'ahorros_afc' => 3000000,
            'ahorro_voluntario_acumulado' => (3800 * $uvt) - 1000000,
        ]);

        $this->assertEquals(1000000, $resultado['ahorro_voluntario_aplicado']);
        $this->assertEquals(500000, $resultado['aportes_pension_voluntaria']);
        $this->assertEquals(500000, $resultado['ahorros_afc']);
    }

    /** @test */
    public function exige_una_uvt_vigente()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $this->servicio()->calcular(['uvt' => 0, 'total_pagos' => 10000000]);
    }

    private function servicio()
    {
        return new RetencionFuenteLaboralService();
    }
}
