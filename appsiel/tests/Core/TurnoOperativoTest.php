<?php

use App\Core\TurnoOperativo;

class TurnoOperativoTest extends TestCase
{
    public function test_fecha_operativa_no_depende_del_dia_del_cierre()
    {
        $turno = new TurnoOperativo(array(
            'fecha_operativa' => '2026-08-22',
            'abierto_en' => '2026-08-22 22:00:00',
            'cerrado_en' => '2026-08-23 06:00:00',
            'estado' => TurnoOperativo::ESTADO_CERRADO,
        ));

        $this->assertSame('2026-08-22', $turno->fecha_operativa);
        $this->assertFalse($turno->estaAbierto());
    }

    public function test_dos_turnos_pueden_compartir_fecha_operativa_sin_compartir_identidad()
    {
        $first = new TurnoOperativo(array('fecha_operativa' => '2026-08-22', 'codigo' => 'TUR-1'));
        $second = new TurnoOperativo(array('fecha_operativa' => '2026-08-22', 'codigo' => 'TUR-2'));

        $this->assertSame($first->fecha_operativa, $second->fecha_operativa);
        $this->assertNotSame($first->codigo, $second->codigo);
    }

    public function test_historico_sin_turno_permanece_sin_asignacion()
    {
        $movement = new \App\Tesoreria\TesoMovimiento(array('fecha' => '2025-01-01'));
        $this->assertNull($movement->turno_operativo_id);
    }
}
