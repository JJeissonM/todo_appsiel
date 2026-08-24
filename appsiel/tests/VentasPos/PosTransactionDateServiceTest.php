<?php

use App\VentasPos\Pdv;
use App\VentasPos\Services\PosTransactionDateService;

class PosTransactionDateServiceTest extends TestCase
{
    public function test_usa_fecha_apertura_sin_aplicar_excepcion_de_acumulacion_en_tiempo_real()
    {
        config([
            'ventas_pos.asignar_fecha_apertura_a_facturas' => 1,
            'ventas_pos.acumular_facturas_en_tiempo_real' => 1
        ]);

        $pdv = $this->getMockBuilder(Pdv::class)
            ->setMethods(['ultima_fecha_apertura'])
            ->getMock();

        $pdv->expects($this->once())
            ->method('ultima_fecha_apertura')
            ->with(false)
            ->willReturn('2026-08-20');

        $fecha = (new PosTransactionDateService())->resolve($pdv, '2026-08-24');

        $this->assertSame('2026-08-20', $fecha);
    }

    public function test_conserva_fecha_enviada_cuando_la_configuracion_esta_inactiva()
    {
        config(['ventas_pos.asignar_fecha_apertura_a_facturas' => 0]);

        $pdv = $this->getMockBuilder(Pdv::class)
            ->setMethods(['ultima_fecha_apertura'])
            ->getMock();

        $pdv->expects($this->never())->method('ultima_fecha_apertura');

        $fecha = (new PosTransactionDateService())->resolve($pdv, '2026-08-24');

        $this->assertSame('2026-08-24', $fecha);
    }

    public function test_usa_fecha_actual_si_no_hay_fecha_y_la_configuracion_esta_inactiva()
    {
        config(['ventas_pos.asignar_fecha_apertura_a_facturas' => 0]);

        $pdv = new Pdv();

        $this->assertSame(date('Y-m-d'), (new PosTransactionDateService())->resolve($pdv));
    }
}
