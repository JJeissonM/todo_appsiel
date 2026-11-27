<?php

use App\Http\Controllers\Nomina\ConsolidadoPrestacionesController;
use App\Nomina\ConsolidadoPrestacionesSociales;
use App\Nomina\PrestacionesLiquidadas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CierreConsolidadosPrestacionesTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.cierre_prestaciones_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'cierre_prestaciones_testing');
        DB::purge('cierre_prestaciones_testing');

        $this->crearEsquema();
    }

    public function tearDown()
    {
        DB::purge('cierre_prestaciones_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function identifica_solamente_prestaciones_liquidadas_por_terminacion_en_el_periodo()
    {
        DB::table('nom_doc_encabezados')->insert([
            ['id' => 10, 'tipo_liquidacion' => 'terminacion_contrato'],
            ['id' => 11, 'tipo_liquidacion' => 'selectiva'],
        ]);

        $this->crearPrestacionLiquidada(10, 25, '2026-06-30', ['cesantias', 'prima_legal']);
        $this->crearPrestacionLiquidada(11, 25, '2026-06-30', ['vacaciones']);

        $this->assertTrue(PrestacionesLiquidadas::existe_prestacion_terminacion_entre_fechas(
            25, 'cesantias', '2026-06-01', '2026-06-30'
        ));
        $this->assertFalse(PrestacionesLiquidadas::existe_prestacion_terminacion_entre_fechas(
            25, 'vacaciones', '2026-06-01', '2026-06-30'
        ));
        $this->assertFalse(PrestacionesLiquidadas::existe_prestacion_terminacion_entre_fechas(
            25, 'cesantias', '2026-07-01', '2026-07-30'
        ));
    }

    /** @test */
    public function el_cierre_cancela_exactamente_el_saldo_de_la_provision()
    {
        $resultado = (new ConsolidadoPrestacionesControllerPruebaCierre())->calcularCierre(
            666668,
            833333,
            166667,
            true
        );

        $this->assertEquals(833335, $resultado['valor_pagado_mes']);
        $this->assertSame(0, $resultado['valor_acumulado']);
        $this->assertSame(0, $resultado['dias_acumulados']);
        $this->assertContains('CIERRE POR TERMINACION DE CONTRATO', $resultado['observacion']);
        $this->assertContains('833333', $resultado['observacion']);
    }

    /** @test */
    public function una_liquidacion_ordinaria_conserva_el_calculo_normal()
    {
        $resultado = (new ConsolidadoPrestacionesControllerPruebaCierre())->calcularCierre(
            100000,
            30000,
            20000,
            false
        );

        $this->assertSame(30000, $resultado['valor_pagado_mes']);
        $this->assertSame(90000, $resultado['valor_acumulado']);
        $this->assertSame('', $resultado['observacion']);
    }

    /** @test */
    public function al_retirar_la_prestacion_desaparece_la_senal_de_cierre()
    {
        DB::table('nom_doc_encabezados')->insert([
            'id' => 10,
            'tipo_liquidacion' => 'terminacion_contrato'
        ]);
        $this->crearPrestacionLiquidada(10, 25, '2026-06-30', ['cesantias', 'prima_legal']);

        PrestacionesLiquidadas::retirar_prestacion(10, 25, 'cesantias');

        $this->assertFalse(PrestacionesLiquidadas::existe_prestacion_terminacion_entre_fechas(
            25, 'cesantias', '2026-06-01', '2026-06-30'
        ));
        $this->assertTrue(PrestacionesLiquidadas::existe_prestacion_terminacion_entre_fechas(
            25, 'prima_legal', '2026-06-01', '2026-06-30'
        ));

        $consolidado = new ConsolidadoPrestacionesSociales([
            'observacion' => ConsolidadoPrestacionesController::OBSERVACION_CIERRE_TERMINACION
        ]);
        $this->assertTrue(
            (new ConsolidadoPrestacionesControllerPruebaCierre())->debeActualizar($consolidado, false)
        );
    }

    protected function crearEsquema()
    {
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo_liquidacion');
        });

        Schema::create('nom_prestaciones_liquidadas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('nom_contrato_id');
            $table->date('fecha_final_promedios');
            $table->text('prestaciones_liquidadas');
            $table->text('datos_liquidacion')->nullable();
            $table->timestamps();
        });
    }

    protected function crearPrestacionLiquidada($documentoId, $contratoId, $fecha, array $prestaciones)
    {
        $datos = [];
        foreach ($prestaciones as $prestacion) {
            $datos[] = (object)['prestacion' => $prestacion, 'tabla_resumen' => (object)[]];
        }

        PrestacionesLiquidadas::create([
            'nom_doc_encabezado_id' => $documentoId,
            'nom_contrato_id' => $contratoId,
            'fecha_final_promedios' => $fecha,
            'prestaciones_liquidadas' => json_encode($datos),
            'datos_liquidacion' => ''
        ]);
    }
}

class ConsolidadoPrestacionesControllerPruebaCierre extends ConsolidadoPrestacionesController
{
    public function calcularCierre($anterior, $pagado, $consolidado, $esTerminacion)
    {
        return $this->aplicar_cierre_por_terminacion($anterior, $pagado, $consolidado, $esTerminacion);
    }

    public function debeActualizar(ConsolidadoPrestacionesSociales $consolidado, $esTerminacion)
    {
        return $this->debe_actualizar_consolidado_existente($consolidado, $esTerminacion);
    }
}
