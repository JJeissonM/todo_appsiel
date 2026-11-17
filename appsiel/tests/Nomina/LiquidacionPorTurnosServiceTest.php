<?php

use App\Nomina\NomDocRegistro;
use App\Nomina\Services\LiquidacionPorTurnosService;
use Illuminate\Support\Facades\Config;

class LiquidacionPorTurnosServiceTest extends TestCase
{
    /** @test */
    public function identifica_unicamente_el_concepto_automatico_de_pago_de_turnos()
    {
        Config::set('nomina.concepto_pago_turnos', 35);

        $registroAutomatico = new NomDocRegistro();
        $registroAutomatico->nom_concepto_id = 35;

        $registroManual = new NomDocRegistro();
        $registroManual->nom_concepto_id = 99;

        $servicio = new LiquidacionPorTurnosService();

        $this->assertTrue($servicio->es_registro_automatico_turnos($registroAutomatico));
        $this->assertFalse($servicio->es_registro_automatico_turnos($registroManual));
    }
}
