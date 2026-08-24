<?php

use App\Inventarios\InvMovimiento;

class InvMovimientoReporteHoraFiltroTest extends TestCase
{
    public function test_sin_horas_conserva_el_filtro_tradicional_por_fechas()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->entreFechasHorasReporteMovimientos('2026-08-12', '2026-08-13');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`fecha` between ? and ?', $sql);
        $this->assertNotContains('created_at', $sql);
        $this->assertSame(['2026-08-12', '2026-08-13'], $query->getBindings());
    }

    public function test_con_horas_filtra_el_rango_y_usa_created_at_para_registros_historicos()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->entreFechasHorasReporteMovimientos('2026-08-12', '2026-08-12', '09:00', '12:00');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`created_at` between ? and ?', $sql);
        $this->assertContains('timestamp(inv_movimientos.fecha, coalesce(inv_movimientos.hora_inicio, inv_movimientos.hora_finalizacion)) >= ?', $sql);
        $this->assertContains('timestamp(inv_movimientos.fecha, coalesce(inv_movimientos.hora_finalizacion, inv_movimientos.hora_inicio)) <= ?', $sql);
        $this->assertSame([
            '2026-08-12 09:00:00',
            '2026-08-12 12:00:00',
            '2026-08-12 09:00:00',
            '2026-08-12 12:00:00'
        ], $query->getBindings());
    }

    public function test_saldo_inicial_con_hora_incluye_lo_anterior_al_inicio_del_rango()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->antesInicioReporteMovimientos('2026-08-12', '09:00');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`created_at` < ?', $sql);
        $this->assertContains('timestamp(inv_movimientos.fecha, coalesce(inv_movimientos.hora_finalizacion, inv_movimientos.hora_inicio)) < ?', $sql);
        $this->assertSame([
            '2026-08-12 09:00:00',
            '2026-08-12 09:00:00'
        ], $query->getBindings());
    }

    public function test_saldo_inicial_sin_hora_conserva_el_corte_por_fecha()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->antesInicioReporteMovimientos('2026-08-12');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`fecha` < ?', $sql);
        $this->assertNotContains('created_at', $sql);
        $this->assertSame(['2026-08-12'], $query->getBindings());
    }

    public function test_existencias_usa_una_unica_hora_como_limite_superior_del_corte()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->hastaFechaHoraCorte('2026-08-12', '14:30');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`fecha` <= ?', $sql);
        $this->assertContains('time(inv_movimientos.created_at) <= ?', $sql);
        $this->assertContains('coalesce(inv_movimientos.hora_finalizacion, inv_movimientos.hora_inicio) <= ?', $sql);
        $this->assertNotContains('coalesce(inv_movimientos.hora_inicio, inv_movimientos.hora_finalizacion) >= ?', $sql);
        $this->assertSame(['2026-08-12', '14:30:00', '14:30:00'], $query->getBindings());
    }

    public function test_existencias_sin_hora_conserva_el_corte_tradicional_por_fecha()
    {
        $query = InvMovimiento::whereRaw('1 = 1');
        $query->hastaFechaHoraCorte('2026-08-12');

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`fecha` <= ?', $sql);
        $this->assertNotContains('coalesce', $sql);
        $this->assertSame(['2026-08-12'], $query->getBindings());
    }

    public function test_balance_incluye_documentos_relacionados_aunque_estan_fuera_del_turno()
    {
        config(['inventarios.usar_inventario_fisico_por_horas' => 1]);

        $query = InvMovimiento::whereRaw('1 = 1');
        $query->duranteTurnoInventarioFisicoIncluyendoDocumentos(
            '2026-08-12',
            '06:00',
            '14:00',
            [501, 502, 501, 0]
        );

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`created_at` between ? and ?', $sql);
        $this->assertContains('or `inv_movimientos`.`inv_doc_encabezado_id` in (?, ?)', $sql);
        $this->assertSame([
            '2026-08-12',
            '2026-08-12',
            '2026-08-12 06:00:00',
            '2026-08-12 14:00:00',
            501,
            502
        ], $query->getBindings());
    }

    public function test_existencia_show_incluye_ajuste_relacionado_despues_del_cierre()
    {
        config(['inventarios.usar_inventario_fisico_por_horas' => 1]);

        $query = InvMovimiento::whereRaw('1 = 1');
        $query->hastaCierreTurnoInventarioFisicoIncluyendoDocumentos(
            '2026-08-12',
            '06:00',
            '14:00',
            [501]
        );

        $sql = strtolower($query->toSql());

        $this->assertContains('`inv_movimientos`.`created_at` <= ?', $sql);
        $this->assertContains('or `inv_movimientos`.`inv_doc_encabezado_id` in (?)', $sql);
        $this->assertSame([
            '2026-08-12',
            '2026-08-12',
            '2026-08-12',
            '2026-08-12 14:00:00',
            501
        ], $query->getBindings());
    }
}
