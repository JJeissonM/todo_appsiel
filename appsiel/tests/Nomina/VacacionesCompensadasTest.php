<?php

use App\Nomina\ModosLiquidacion\PrestacionesSociales\Vacaciones;

class VacacionesCompensadasTest extends TestCase
{
    /** @test */
    public function separa_el_valor_disfrutado_del_compensado_y_conserva_el_total_para_salario_fijo()
    {
        $libro = (object) [
            'dias_pagados' => 15,
            'dias_disfrutados' => 8,
            'dias_compensados' => 7,
            'dias_no_habiles' => 1,
        ];

        $valores = (new Vacaciones())->calcular_valores_vacaciones_programadas(
            $libro,
            100000,
            100000
        );

        $this->assertSame(800000.0, $valores['vlr_dias_disfrutados']);
        $this->assertSame(700000.0, $valores['vlr_dias_compensados']);
        $this->assertSame(100000.0, $valores['vlr_dias_no_habiles']);
        $this->assertSame(1600000.0, $valores['valor_total_vacaciones']);
    }

    /** @test */
    public function usa_una_base_independiente_para_la_compensacion_en_dinero()
    {
        $libro = (object) [
            'dias_pagados' => 15,
            'dias_disfrutados' => 8,
            'dias_compensados' => 7,
            'dias_no_habiles' => 1,
        ];

        $valores = (new Vacaciones())->calcular_valores_vacaciones_programadas(
            $libro,
            90000,
            100000
        );

        $this->assertSame(720000.0, $valores['vlr_dias_disfrutados']);
        $this->assertSame(700000.0, $valores['vlr_dias_compensados']);
        $this->assertSame(1510000.0, $valores['valor_total_vacaciones']);
    }
}
