<?php

use App\Core\TurnoOperativo;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class TesoMovimientoSaldoInicialArqueoTest extends TestCase
{
    use DatabaseTransactions;

    protected $configuracion_horas_original;

    protected function setUp()
    {
        parent::setUp();
        $this->configuracion_horas_original = config('tesoreria.usar_movimientos_tesoreria_por_hora');
    }

    protected function tearDown()
    {
        config(['tesoreria.usar_movimientos_tesoreria_por_hora' => $this->configuracion_horas_original]);
        parent::tearDown();
    }

    public function test_sin_apertura_calcula_solo_movimientos_anteriores_al_dia()
    {
        config(['tesoreria.usar_movimientos_tesoreria_por_hora' => 1]);

        $this->crearMovimiento(991, 881, '2026-08-20', '2026-08-20 18:00:00', 150);
        $this->crearMovimiento(991, 881, '2026-08-21', '2026-08-21 05:00:00', 50);

        $saldo = TesoMovimiento::calcularSaldoInicialArqueo(991, 881, '2026-08-21');

        $this->assertSame(150.0, $saldo);
    }

    public function test_con_apertura_incluye_movimientos_del_dia_anteriores_a_la_hora()
    {
        config(['tesoreria.usar_movimientos_tesoreria_por_hora' => 1]);

        $this->crearMovimiento(992, 882, '2026-08-20', '2026-08-20 18:00:00', 100);
        $this->crearMovimiento(992, 882, '2026-08-21', '2026-08-21 05:30:00', 40);
        $this->crearMovimiento(992, 882, '2026-08-21', '2026-08-21 06:00:00', 20);
        $this->crearMovimiento(992, 883, '2026-08-20', '2026-08-20 18:00:00', 500);
        $this->crearMovimiento(993, 882, '2026-08-20', '2026-08-20 18:00:00', 700);

        $saldo = TesoMovimiento::calcularSaldoInicialArqueo(
            992,
            882,
            '2026-08-21',
            '2026-08-21 06:00:00'
        );

        $this->assertSame(140.0, $saldo);
    }

    public function test_ignora_la_hora_cuando_el_manejo_por_horas_esta_desactivado()
    {
        config(['tesoreria.usar_movimientos_tesoreria_por_hora' => 0]);

        $this->crearMovimiento(994, 884, '2026-08-20', '2026-08-20 18:00:00', 80);
        $this->crearMovimiento(994, 884, '2026-08-21', '2026-08-21 05:30:00', 30);

        $saldo = TesoMovimiento::calcularSaldoInicialArqueo(
            994,
            884,
            '2026-08-21',
            '2026-08-21 06:00:00'
        );

        $this->assertSame(80.0, $saldo);
    }

    public function test_turno_incluye_transacciones_posteriores_asociadas_a_turnos_anteriores()
    {
        $turnoAnterior = $this->crearTurno(995, 885, '2026-09-14', '2026-09-14 08:00:00', 400);
        $turnoArqueo = $this->crearTurno(995, 885, '2026-09-15', '2026-09-15 08:00:00', 500);

        // Fue registrada el 16, pero contable y operativamente pertenece al
        // turno anterior. No estaba incluida cuando abrió el turno del arqueo.
        $this->crearMovimiento(995, 885, '2026-09-14', '2026-09-16 10:00:00', 75, $turnoAnterior->id);

        // Pertenece al propio turno arqueado: se mostrará entre sus movimientos
        // y no debe sumarse también al saldo inicial.
        $this->crearMovimiento(995, 885, '2026-09-15', '2026-09-16 11:00:00', 30, $turnoArqueo->id);
        $this->crearMovimiento(995, 886, '2026-09-14', '2026-09-16 12:00:00', 900, $turnoAnterior->id);

        $saldo = TesoMovimiento::calcularSaldoInicialArqueoPorTurno(995, 885, $turnoArqueo);

        $this->assertSame(575.0, $saldo);
    }

    protected function crearMovimiento($empresa_id, $caja_id, $fecha, $created_at, $valor, $turno_id = null)
    {
        DB::table('teso_movimientos')->insert([
            'fecha' => $fecha,
            'core_empresa_id' => $empresa_id,
            'core_tercero_id' => 1,
            'codigo_referencia_tercero' => '',
            'core_tipo_transaccion_id' => 1,
            'core_tipo_doc_app_id' => 1,
            'consecutivo' => 1,
            'teso_motivo_id' => 1,
            'teso_caja_id' => $caja_id,
            'teso_cuenta_bancaria_id' => 0,
            'pdv_id' => null,
            'valor_movimiento' => $valor,
            'documento_soporte' => '',
            'descripcion' => 'Movimiento de prueba',
            'estado' => 'Activo',
            'creado_por' => 'test@appsiel.com',
            'modificado_por' => 'test@appsiel.com',
            'created_at' => $created_at,
            'updated_at' => $created_at,
            'teso_medio_recaudo_id' => 1,
            'turno_operativo_id' => $turno_id
        ]);
    }

    protected function crearTurno($empresa_id, $caja_id, $fecha, $apertura, $saldo_inicial)
    {
        return TurnoOperativo::create([
            'core_empresa_id' => $empresa_id,
            'contexto_tipo' => 'caja',
            'contexto_id' => $caja_id,
            'pdv_id' => null,
            'teso_caja_id' => $caja_id,
            'fecha_operativa' => $fecha,
            'abierto_en' => $apertura,
            'cerrado_en' => date('Y-m-d H:i:s', strtotime($apertura . ' +8 hours')),
            'abierto_por' => 1,
            'cerrado_por' => 1,
            'saldo_inicial' => $saldo_inicial,
            'saldo_cierre' => $saldo_inicial,
            'estado' => TurnoOperativo::ESTADO_CERRADO,
            'codigo' => 'TEST-SALDO-' . uniqid(),
            'clave_contexto_abierto' => null,
            'observaciones' => 'Prueba de recálculo de saldo inicial'
        ]);
    }
}
