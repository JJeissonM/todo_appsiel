<?php

use App\Tesoreria\TesoMovimiento;

class TesoMovimientoHoraFiltroTest extends TestCase
{
    public function test_normaliza_horas_del_formulario()
    {
        $this->assertSame('08:30:00', TesoMovimiento::normalizarHora('08:30'));
        $this->assertSame('08:30:59', TesoMovimiento::normalizarHora('08:30', true));
        $this->assertSame('18:45:59', TesoMovimiento::normalizarHora('18:45:59'));
        $this->assertNull(TesoMovimiento::normalizarHora(''));
        $this->assertNull(TesoMovimiento::normalizarHora('25:00'));
    }

    public function test_filtra_ambas_horas_en_una_misma_fecha()
    {
        $query = TesoMovimiento::query();
        TesoMovimiento::aplicarFiltroEntreFechasHoras($query, '2026-08-13', '2026-08-13', '08:00', '17:00');

        $this->assertContains('time(`teso_movimientos`.`created_at`) >= ?', strtolower($query->toSql()));
        $this->assertContains('time(`teso_movimientos`.`created_at`) <= ?', strtolower($query->toSql()));
        $this->assertSame(['2026-08-13', '08:00:00', '17:00:59'], $query->getBindings());
    }

    public function test_saldo_inicial_incluye_movimientos_anteriores_del_dia_inicial()
    {
        $query = TesoMovimiento::query();
        TesoMovimiento::aplicarFiltroAntesDeFechaHora($query, '2026-08-13', '08:00');

        $this->assertContains('`teso_movimientos`.`fecha` < ?', $query->toSql());
        $this->assertContains('time(`teso_movimientos`.`created_at`) < ?', strtolower($query->toSql()));
        $this->assertSame(['2026-08-13', '2026-08-13', '08:00:00'], $query->getBindings());
    }

    public function test_aplica_solo_hora_desde_cuando_hora_hasta_esta_vacia()
    {
        $query = TesoMovimiento::query();
        TesoMovimiento::aplicarFiltroEntreFechasHoras($query, '2026-08-13', '2026-08-13', '08:00', null);

        $sql = strtolower($query->toSql());
        $this->assertContains('time(`teso_movimientos`.`created_at`) >= ?', $sql);
        $this->assertNotContains('time(`teso_movimientos`.`created_at`) <= ?', $sql);
        $this->assertSame(['2026-08-13', '08:00:00'], $query->getBindings());
    }

    public function test_aplica_solo_hora_hasta_cuando_hora_desde_esta_vacia()
    {
        $query = TesoMovimiento::query();
        TesoMovimiento::aplicarFiltroEntreFechasHoras($query, '2026-08-13', '2026-08-13', null, '17:00');

        $sql = strtolower($query->toSql());
        $this->assertNotContains('time(`teso_movimientos`.`created_at`) >= ?', $sql);
        $this->assertContains('time(`teso_movimientos`.`created_at`) <= ?', $sql);
        $this->assertSame(['2026-08-13', '17:00:59'], $query->getBindings());
    }

    public function test_en_varios_dias_aplica_las_horas_solo_en_los_extremos()
    {
        $query = TesoMovimiento::query();
        TesoMovimiento::aplicarFiltroEntreFechasHoras($query, '2026-08-13', '2026-08-15', '08:00', '17:00');

        $this->assertSame([
            '2026-08-13',
            '08:00:00',
            '2026-08-13',
            '2026-08-15',
            '2026-08-15',
            '17:00:59'
        ], $query->getBindings());
    }
}
