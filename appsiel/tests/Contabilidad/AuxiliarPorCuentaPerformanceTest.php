<?php

use App\Contabilidad\ContabMovimiento;
use App\Http\Controllers\Contabilidad\ContabReportesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuxiliarPorCuentaPerformanceTest extends TestCase
{
    private $previousConnection;

    protected function setUp()
    {
        parent::setUp();
        $this->previousConnection = DB::getDefaultConnection();
        config(['database.connections.auxiliar_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('auxiliar_test');
        Auth::shouldReceive('user')->andReturn((object)['empresa_id' => 1]);
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER, codigo TEXT, descripcion TEXT, contab_cuenta_clase_id INTEGER, contab_cuenta_grupo_id INTEGER)');
        DB::statement('CREATE TABLE core_terceros (id INTEGER, descripcion TEXT, numero_identificacion TEXT)');
        DB::statement('CREATE TABLE core_tipos_docs_apps (id INTEGER, prefijo TEXT)');
        DB::statement('CREATE TABLE contab_movimientos (id INTEGER, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, contab_cuenta_id INTEGER, core_tercero_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, fecha TEXT, created_at TEXT, valor_debito REAL, valor_credito REAL, valor_saldo REAL, detalle_operacion TEXT)');
        DB::table('contab_cuentas')->insert([
            ['id' => 1, 'codigo' => '1105', 'descripcion' => 'Caja prueba', 'contab_cuenta_clase_id' => 1, 'contab_cuenta_grupo_id' => 11],
            ['id' => 2, 'codigo' => '2105', 'descripcion' => 'Otra clase', 'contab_cuenta_clase_id' => 2, 'contab_cuenta_grupo_id' => 21]
        ]);
        DB::table('core_terceros')->insert(['id' => 1, 'descripcion' => 'Tercero prueba', 'numero_identificacion' => '123']);
        DB::table('core_tipos_docs_apps')->insert(['id' => 1, 'prefijo' => 'CC']);
        $row = ['core_tipo_transaccion_id' => 1, 'id' => 0, 'core_empresa_id' => 1, 'contab_cuenta_id' => 1, 'core_tercero_id' => 1, 'core_tipo_doc_app_id' => 1, 'consecutivo' => 1, 'fecha' => '2026-08-31', 'created_at' => '2026-08-31 10:00:00', 'valor_debito' => 100, 'valor_credito' => 0, 'valor_saldo' => 100, 'detalle_operacion' => 'Prueba'];
        DB::table('contab_movimientos')->insert($row);
        $row['fecha'] = '2026-09-01';
        for ($i = 1; $i <= 100; $i++) {
            $row['id'] = $i;
            DB::table('contab_movimientos')->insert($row);
        }
        $row['id'] = 101; $row['core_empresa_id'] = 2;
        DB::table('contab_movimientos')->insert($row);
        $row['id'] = 102; $row['core_empresa_id'] = 1; $row['contab_cuenta_id'] = 2;
        DB::table('contab_movimientos')->insert($row);
    }

    protected function tearDown()
    {
        DB::purge('auxiliar_test');
        DB::setDefaultConnection($this->previousConnection);
        parent::tearDown();
    }

    public function test_reporte_agrupado_y_sin_agrupar_usan_cinco_consultas_para_cien_movimientos()
    {
        foreach ([0, 1] as $grouped) {
            DB::enableQueryLog(); DB::flushQueryLog();
            $html = (new ContabReportesController())->contab_ajax_auxiliar_por_cuenta(new Request([
                'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-15',
                'clase_cuenta_id' => 1, 'agrupar_por_cuenta' => $grouped
            ]));
            $this->assertCount(5, DB::getQueryLog());
            $this->assertContains('10.100', $html);
            $this->assertContains('Tercero prueba', $html);
            $this->assertNotContains('Otra clase', $html);
        }
    }

    public function test_filtros_de_cuenta_grupo_y_tercero_conservan_saldos_y_movimientos()
    {
        foreach ([[1, null, null, null], [null, null, 11, null], [1, 1, null, null]] as $filters) {
            list($account, $third, $group, $class) = $filters;
            $rows = ContabMovimiento::get_movimiento_contable('2026-09-01', '2026-09-15', $account, $third, $group, $class);
            $this->assertCount(100, $rows);
            $this->assertEquals(100, ContabMovimiento::get_saldo_inicial_v2('2026-09-01', $account, $third, $group, $class));
            $this->assertEquals([1 => 100], ContabMovimiento::get_saldos_iniciales_por_cuenta('2026-09-01', $account, $third, $group, $class));
        }
    }
    public function test_totalizar_conserva_saldo_y_funciona_con_ambas_agrupaciones()
    {
        $rows = ContabMovimiento::get_movimiento_contable('2026-09-01', '2026-09-15', 1, null, null, null, true);
        $this->assertCount(1, $rows);
        $this->assertEquals(10000, $rows->first()->valor_debito);
        $this->assertEquals(10000, $rows->first()->valor_saldo);
        foreach ([0, 1] as $grouped) {
            DB::enableQueryLog(); DB::flushQueryLog();
            $html = (new ContabReportesController())->contab_ajax_auxiliar_por_cuenta(new Request([
                'fecha_desde' => '2026-09-01', 'fecha_hasta' => '2026-09-15',
                'contab_cuenta_id' => 1, 'agrupar_por_cuenta' => $grouped, 'totalizar_por_documento' => 1
            ]));
            $this->assertCount(5, DB::getQueryLog());
            $this->assertContains('10.100', $html);
            $this->assertEquals(1, substr_count($html, 'Tercero prueba'));
        }
    }

    public function test_cada_componente_de_la_clave_mantiene_documentos_separados()
    {
        $original = (array)DB::table('contab_movimientos')->where('id', 1)->first();
        $id = 200;
        foreach (['core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo', 'core_tercero_id', 'contab_cuenta_id'] as $field) {
            $row = $original;
            $row['id'] = $id++;
            $row[$field] = 99;
            DB::table('contab_movimientos')->insert($row);
        }
        $rows = ContabMovimiento::get_movimiento_contable('2026-09-01', '2026-09-15', null, null, null, null, true);
        // Base, otra cuenta de la fixture y cinco combinaciones diferentes.
        $this->assertCount(7, $rows);
        $this->assertEquals(10600, $rows->sum('valor_debito'));
    }

    public function test_suma_creditos_con_signo_y_no_atribuye_un_detalle_unico()
    {
        DB::table('contab_movimientos')->where('id', 1)->update([
            'valor_debito' => 0, 'valor_credito' => -40, 'valor_saldo' => -40,
            'detalle_operacion' => 'Otro detalle'
        ]);
        $row = ContabMovimiento::get_movimiento_contable('2026-09-01', '2026-09-15', 1, null, null, null, true)->first();
        $this->assertEquals(9900, $row->valor_debito);
        $this->assertEquals(-40, $row->valor_credito);
        $this->assertEquals(9860, $row->valor_saldo);
        $this->assertSame('Varios detalles', $row->detalle_operacion);
    }
}
