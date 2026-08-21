<?php

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

    protected function crearMovimiento($empresa_id, $caja_id, $fecha, $created_at, $valor)
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
            'teso_medio_recaudo_id' => 1
        ]);
    }
}
