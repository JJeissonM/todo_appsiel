<?php

use App\Tesoreria\TesoMovimiento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TesoMovimientoHoraFiltroTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_incluye_los_movimientos_de_dias_intermedios_sin_filtrar_la_hora()
    {
        $empresaId = 999997;
        $excluidoPrimerDia = $this->crearMovimiento($empresaId, '2026-08-13', '2026-08-13 07:30:00');
        $incluidoPrimerDia = $this->crearMovimiento($empresaId, '2026-08-13', '2026-08-13 09:00:00');
        $incluidoDiaIntermedio = $this->crearMovimiento($empresaId, '2026-08-14', '2026-08-14 07:30:00');
        $incluidoUltimoDia = $this->crearMovimiento($empresaId, '2026-08-15', '2026-08-15 16:30:00');
        $excluidoUltimoDia = $this->crearMovimiento($empresaId, '2026-08-15', '2026-08-15 17:30:00');

        $query = TesoMovimiento::where('core_empresa_id', $empresaId);
        TesoMovimiento::aplicarFiltroEntreFechasHoras($query, '2026-08-13', '2026-08-15', '08:00', '17:00');
        $ids = $query->get()->pluck('id')->toArray();

        $this->assertContains($incluidoPrimerDia, $ids);
        $this->assertContains($incluidoDiaIntermedio, $ids);
        $this->assertContains($incluidoUltimoDia, $ids);
        $this->assertNotContains($excluidoPrimerDia, $ids);
        $this->assertNotContains($excluidoUltimoDia, $ids);
    }

    protected function crearMovimiento($empresaId, $fecha, $createdAt)
    {
        return DB::table('teso_movimientos')->insertGetId(array(
            'fecha' => $fecha,
            'core_empresa_id' => $empresaId,
            'core_tercero_id' => 1,
            'codigo_referencia_tercero' => '',
            'core_tipo_transaccion_id' => 1,
            'core_tipo_doc_app_id' => 1,
            'consecutivo' => 1,
            'teso_motivo_id' => 1,
            'teso_caja_id' => 1,
            'teso_cuenta_bancaria_id' => 0,
            'pdv_id' => null,
            'valor_movimiento' => 100,
            'documento_soporte' => '',
            'descripcion' => 'Movimiento de prueba para filtro por hora',
            'estado' => 'Activo',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'teso_medio_recaudo_id' => 1,
        ));
    }
}
