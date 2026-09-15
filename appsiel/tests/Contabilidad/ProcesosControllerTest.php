<?php

use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Http\Controllers\Contabilidad\ProcesosController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ProcesosControllerTest extends TestCase
{
    private $conexionOriginal;

    protected function setUp()
    {
        parent::setUp();
        $this->conexionOriginal = DB::getDefaultConnection();
        config(['database.connections.cierre_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        DB::setDefaultConnection('cierre_test');
        Auth::shouldReceive('user')->andReturn((object) ['empresa_id' => 1, 'email' => 'prueba@example.com']);
        config(['contabilidad.cuenta_ganancias_perdidas_ejercicio' => 9]);
        DB::statement('CREATE TABLE contab_periodos_ejercicio (id INTEGER, core_empresa_id INTEGER, fecha_desde TEXT, fecha_hasta TEXT, descripcion TEXT)');
        DB::statement("INSERT INTO contab_periodos_ejercicio VALUES (1, 1, '2026-01-01', '2026-12-31', '2026'), (2, 2, '2026-01-01', '2026-12-31', 'Otra empresa')");
        DB::statement('CREATE TABLE contab_cuentas (id INTEGER, contab_cuenta_clase_id INTEGER)');
        DB::statement('INSERT INTO contab_cuentas VALUES (4, 4), (5, 5), (6, 6), (7, 7), (1, 1), (9, 3)');
        DB::statement('CREATE TABLE contab_movimientos (contab_cuenta_id INTEGER, core_empresa_id INTEGER, fecha TEXT, valor_saldo NUMERIC)');
        DB::statement("INSERT INTO contab_movimientos VALUES (4, 1, '2026-06-01', -100), (4, 1, '2026-06-02', -50), (5, 1, '2026-06-01', 60), (6, 1, '2026-06-01', 0), (7, 1, '2026-06-01', 10), (4, 2, '2026-06-01', -999), (5, 1, '2025-06-01', 999), (1, 1, '2026-06-01', 999)");
        DB::statement('CREATE TABLE encabezados_prueba (id INTEGER)');
    }

    protected function tearDown()
    {
        DB::purge('cierre_test');
        DB::setDefaultConnection($this->conexionOriginal);
        parent::tearDown();
    }

    public function test_listado_agrupa_saldos_y_filtra_periodo_empresa_y_clases()
    {
        View::shouldReceive('make')->once()->with(
            'contabilidad.procesos.cierre_ejercicio_tabla_saldos_cuentas_resultados',
            Mockery::on(function ($datos) {
                $movimientos = $datos['lista_movimientos'];
                $this->assertCount(4, $movimientos);
                $this->assertEquals([-150, 60, 0, 10], $movimientos->pluck('valor_saldo')->all());
                foreach ($movimientos as $movimiento) {
                    $this->assertTrue($movimiento->relationLoaded('cuenta'));
                }
                return true;
            })
        )->andReturn(Mockery::mock(['render' => 'listado']));
        $this->assertSame('listado', (new ProcesosController())->generar_listado_cierre_ejercicio(new Request(['periodo_ejercicio_id' => 1])));
    }

    public function test_crea_asientos_opuestos_balanceados_con_la_fecha_del_periodo()
    {
        $contabilidad = Mockery::mock(ContabilidadController::class);
        $this->app->instance(ContabilidadController::class, $contabilidad);
        $contabilidad->shouldReceive('crear_encabezado_documento')->once()->andReturn((object) ['id' => 123]);
        $contabilidad->shouldReceive('almacenar_lineas_registros')->once()->andReturnUsing(function ($request, $lineas, $encabezado) {
            $this->assertCount(8, $lineas);
            $this->assertEquals(80, $request->valor_total);
            $this->assertEquals($lineas, json_decode($request->tabla_registros_documento));
            $debitos = 0;
            $creditos = 0;
            foreach (array_slice($lineas, 0, -2) as $linea) {
                $this->assertSame('2026-12-31', $linea->fecha_vencimiento);
                $debitos += (float) substr($linea->debito, 1);
                $creditos += (float) substr($linea->credito, 1);
            }
            $this->assertEquals(220, $debitos);
            $this->assertEquals($debitos, $creditos);
            $this->assertEquals(150, (float) substr($lineas[0]->debito, 1));
            $this->assertEquals(60, (float) substr($lineas[2]->credito, 1));
            return true;
        });
        $response = (new ProcesosController())->crear_nota_cierre_ejercicio(new Request(['periodo_ejercicio_id2' => 1]));
        $this->assertContains('contabilidad/123?', $response->getTargetUrl());
    }

    public function test_no_crea_documentos_sin_saldos()
    {
        DB::table('contab_movimientos')->update(['valor_saldo' => 0]);
        $contabilidad = Mockery::mock(ContabilidadController::class);
        $contabilidad->shouldNotReceive('crear_encabezado_documento');
        $this->app->instance(ContabilidadController::class, $contabilidad);
        $response = (new ProcesosController())->crear_nota_cierre_ejercicio(new Request(['periodo_ejercicio_id2' => 1]));
        $this->assertSame('No hay saldos de cuentas de resultado para cerrar.', $response->getSession()->get('mensaje_error'));
    }

    public function test_revierte_el_encabezado_si_falla_el_guardado_de_lineas()
    {
        $contabilidad = Mockery::mock(ContabilidadController::class);
        $this->app->instance(ContabilidadController::class, $contabilidad);
        $contabilidad->shouldReceive('crear_encabezado_documento')->once()->andReturnUsing(function () {
            DB::table('encabezados_prueba')->insert(['id' => 123]);
            return (object) ['id' => 123];
        });
        $contabilidad->shouldReceive('almacenar_lineas_registros')->once()->andThrow(new RuntimeException('Error de guardado'));
        try {
            (new ProcesosController())->crear_nota_cierre_ejercicio(new Request(['periodo_ejercicio_id2' => 1]));
            $this->fail('Se esperaba el error de guardado.');
        } catch (RuntimeException $e) {
            $this->assertSame('Error de guardado', $e->getMessage());
            $this->assertEquals(0, DB::table('encabezados_prueba')->count());
        }
    }

    public function test_ruta_crea_cierre_con_mil_cuentas_y_cien_mil_movimientos()
    {
        // Base aislada: se ejecuta el guardado real sin alterar datos contables locales.
        DB::statement('DROP TABLE contab_movimientos');
        foreach ([new App\Contabilidad\ContabMovimiento, new App\Contabilidad\ContabDocRegistro,
            new App\Contabilidad\ContabDocEncabezado] as $modelo) {
            Illuminate\Support\Facades\Schema::create($modelo->getTable(), function ($tabla) use ($modelo) {
                $tabla->increments('id');
                foreach ($modelo->getFillable() as $campo) {
                    if (in_array($campo, ['valor_debito', 'valor_credito', 'valor_saldo', 'valor_total'])) {
                        $tabla->decimal($campo, 18, 2)->nullable();
                    } else {
                        $tabla->string($campo)->nullable();
                    }
                }
                $tabla->timestamps();
            });
        }
        DB::statement('CREATE TABLE sys_modelos (id INTEGER, name_space TEXT)');
        DB::table('sys_modelos')->insert(['id' => 47, 'name_space' => 'App\\Contabilidad\\ContabDocEncabezado']);
        $consecutivos = (new App\Core\ConsecutivoDocumento)->getTable();
        Illuminate\Support\Facades\Schema::create($consecutivos, function ($tabla) {
            $tabla->increments('id');
            $tabla->integer('core_empresa_id');
            $tabla->integer('core_documento_app_id');
            $tabla->integer('consecutivo_actual');
            $tabla->timestamps();
        });
        config([
            'contabilidad.tipo_documento_cierre_ejercicio' => 1,
            'contabilidad.transaccion_default_cierre_ejercicio' => 9,
            'contabilidad.tercero_default_cierre_ejercicio' => 1
        ]);
        DB::disableQueryLog();
        DB::transaction(function () {
            for ($i = 10; $i < 1010; $i++) {
                DB::table('contab_cuentas')->insert(['id' => $i, 'contab_cuenta_clase_id' => 4 + ($i % 4)]);
                $filas = array_fill(0, 100, [
                    'contab_cuenta_id' => $i, 'core_empresa_id' => 1,
                    'fecha' => '2026-06-01', 'valor_saldo' => $i % 2 ? -1 : 1
                ]);
                DB::table('contab_movimientos')->insert($filas);
            }
        });

        $inicio = microtime(true);
        $this->withoutMiddleware();
        $respuesta = $this->call('POST', '/contab_crear_nota_cierre_ejercicio', ['periodo_ejercicio_id2' => 1]);
        $duracion = microtime(true) - $inicio;

        $this->assertEquals(302, $respuesta->getStatusCode(), $respuesta->getContent());
        $this->assertContains('/contabilidad/1?', $respuesta->headers->get('Location'));
        $this->assertEquals(1, DB::table('contab_doc_encabezados')->count());
        $this->assertEquals(2000, DB::table('contab_doc_registros')->count());
        $this->assertEquals(102000, DB::table('contab_movimientos')->count());
        $this->assertEquals(100000, DB::table('contab_doc_registros')->sum('valor_debito'));
        $this->assertEquals(100000, DB::table('contab_doc_registros')->sum('valor_credito'));
        $this->assertEquals(0, DB::table('contab_movimientos')->sum('valor_saldo'));
        $this->assertEquals(1, DB::table($consecutivos)->value('consecutivo_actual'));
        fwrite(STDERR, sprintf("\nCierre de prueba: %.2f s; memoria máxima del proceso: %.1f MiB.\n", $duracion, memory_get_peak_usage(true) / 1048576));
    }

    public function test_ambos_metodos_rechazan_periodos_de_otra_empresa()
    {
        foreach (['generar_listado_cierre_ejercicio' => 'periodo_ejercicio_id', 'crear_nota_cierre_ejercicio' => 'periodo_ejercicio_id2'] as $metodo => $campo) {
            try {
                (new ProcesosController())->$metodo(new Request([$campo => 2]));
                $this->fail('Se esperaba rechazar el periodo.');
            } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->assertSame('App\\Contabilidad\\ContabPeriodoEjercicio', $e->getModel());
            }
        }
    }
}
