<?php

use App\Nomina\ModosLiquidacion\Estrategias\PrestacionSocial;
use App\Nomina\NomContrato;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PrestacionesDocentesTest extends TestCase
{
    protected $conexionOriginal;
    protected $configDocentesParcialOriginal;
    protected $configPrimaCompletaOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        $this->configDocentesParcialOriginal = Config::get(
            'nomina.docentes_prestaciones_contrato_parcial'
        );
        $this->configPrimaCompletaOriginal = Config::get(
            'nomina.pagar_prima_completa_docentes'
        );
        Config::set('database.connections.prestaciones_docentes_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'prestaciones_docentes_testing');
        Config::set(
            'nomina.docentes_prestaciones_contrato_parcial',
            NomContrato::DOCENTE_PRESTACIONES_PARCIAL_TIEMPO_REAL
        );
        Config::set('nomina.pagar_prima_completa_docentes', '0');
        DB::purge('prestaciones_docentes_testing');

        $this->crearEsquema();
    }

    public function tearDown()
    {
        DB::purge('prestaciones_docentes_testing');
        Config::set('database.default', $this->conexionOriginal);
        Config::set(
            'nomina.docentes_prestaciones_contrato_parcial',
            $this->configDocentesParcialOriginal
        );
        Config::set(
            'nomina.pagar_prima_completa_docentes',
            $this->configPrimaCompletaOriginal
        );
        parent::tearDown();
    }

    /** @test */
    public function equipara_a_360_dias_el_contrato_docente_que_cubre_el_anio_escolar()
    {
        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-11-30',
        ]);

        $this->assertSame(60, $contrato->dias_laborados_adicionales_docentes(
            '2026-02-01',
            '2026-11-30'
        ));
    }

    /** @test */
    public function no_extiende_contratos_parciales_ni_periodos_que_no_cubren_el_anio_escolar()
    {
        $contratoParcial = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-06-30',
        ]);
        $contratoCompleto = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-11-30',
        ]);

        $this->assertSame(0, $contratoParcial->dias_laborados_adicionales_docentes(
            '2026-02-01',
            '2026-06-30'
        ));
        $this->assertSame(0, $contratoCompleto->dias_laborados_adicionales_docentes(
            '2026-07-01',
            '2026-11-30'
        ));
    }

    /** @test */
    public function puede_proyectar_proporcionalmente_un_contrato_docente_parcial()
    {
        Config::set(
            'nomina.docentes_prestaciones_contrato_parcial',
            NomContrato::DOCENTE_PRESTACIONES_PARCIAL_PROPORCIONAL
        );

        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-03-30',
        ]);

        $diasAdicionales = $contrato->dias_laborados_adicionales_docentes(
            '2026-02-01',
            '2026-03-30',
            60
        );

        $this->assertSame(12, $diasAdicionales);
        $this->assertSame(72, 60 + $diasAdicionales);
    }

    /** @test */
    public function la_configuracion_parcial_no_cambia_la_equivalencia_del_anio_escolar_completo()
    {
        Config::set(
            'nomina.docentes_prestaciones_contrato_parcial',
            NomContrato::DOCENTE_PRESTACIONES_PARCIAL_PROPORCIONAL
        );

        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-11-30',
        ]);

        $this->assertSame(60, $contrato->dias_laborados_adicionales_docentes(
            '2026-02-01',
            '2026-11-30',
            300
        ));
    }

    /** @test */
    public function puede_pagar_prima_completa_en_cada_periodo_del_anio_escolar()
    {
        Config::set('nomina.pagar_prima_completa_docentes', '1');

        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-11-30',
        ]);

        $this->assertSame(30, $contrato->dias_prima_adicionales_docentes(150));
        $this->assertSame(180, 150 + $contrato->dias_prima_adicionales_docentes(150));
    }

    /** @test */
    public function prima_completa_respeta_la_configuracion_de_contratos_parciales()
    {
        Config::set('nomina.pagar_prima_completa_docentes', '1');

        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'fecha_ingreso' => '2026-02-01',
            'contrato_hasta' => '2026-03-30',
        ]);

        $this->assertSame(0, $contrato->dias_prima_adicionales_docentes(60));

        Config::set(
            'nomina.docentes_prestaciones_contrato_parcial',
            NomContrato::DOCENTE_PRESTACIONES_PARCIAL_PROPORCIONAL
        );

        $this->assertSame(12, $contrato->dias_prima_adicionales_docentes(60));
    }

    /** @test */
    public function usa_el_ultimo_salario_mensual_estable_como_base_del_docente()
    {
        DB::table('nom_agrupaciones_conceptos')->insert(['id' => 9]);
        DB::table('nom_conceptos')->insert([
            ['id' => 1],
            ['id' => 2],
        ]);
        DB::table('nom_agrupacion_tiene_conceptos')->insert([
            ['nom_agrupacion_id' => 9, 'nom_concepto_id' => 1],
            ['nom_agrupacion_id' => 9, 'nom_concepto_id' => 2],
        ]);
        DB::table('nom_parametros_liquidacion_prestaciones_sociales')->insert([
            'concepto_prestacion' => 'vacaciones',
            'grupo_empleado_id' => 4,
            'nom_concepto_id' => 99,
        ]);

        foreach (['2026-09-30', '2026-10-30', '2026-11-30'] as $fecha) {
            DB::table('nom_doc_registros')->insert([
                [
                    'nom_doc_encabezado_id' => 1,
                    'nom_contrato_id' => 56,
                    'nom_concepto_id' => 1,
                    'fecha' => $fecha,
                    'valor_devengo' => 1750905,
                    'valor_deduccion' => 0,
                ],
                [
                    'nom_doc_encabezado_id' => 1,
                    'nom_contrato_id' => 56,
                    'nom_concepto_id' => 2,
                    'fecha' => $fecha,
                    'valor_devengo' => 249095,
                    'valor_deduccion' => 0,
                ],
            ]);
        }

        $contrato = new NomContrato([
            'tipo_cotizante' => '22',
            'grupo_empleado_id' => 4,
        ]);
        $contrato->id = 56;

        $baseDiaria = PrestacionSocial::get_valor_base_diaria_docente_salario_estable(
            $contrato,
            9,
            '2026-02-01',
            '2026-11-30'
        );

        $this->assertEquals(2000000 / 30, $baseDiaria, '', 0.001);
    }

    protected function crearEsquema()
    {
        Schema::create('nom_agrupaciones_conceptos', function (Blueprint $table) {
            $table->increments('id');
        });
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
        });
        Schema::create('nom_agrupacion_tiene_conceptos', function (Blueprint $table) {
            $table->integer('nom_agrupacion_id');
            $table->integer('nom_concepto_id');
        });
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo_liquidacion')->nullable();
        });
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('nom_contrato_id');
            $table->integer('nom_concepto_id');
            $table->date('fecha');
            $table->decimal('valor_devengo', 20, 4)->default(0);
            $table->decimal('valor_deduccion', 20, 4)->default(0);
        });
        Schema::create('nom_parametros_liquidacion_prestaciones_sociales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concepto_prestacion');
            $table->integer('grupo_empleado_id');
            $table->integer('nom_concepto_id');
        });

        DB::table('nom_doc_encabezados')->insert([
            'id' => 1,
            'tipo_liquidacion' => 'normal',
        ]);
    }
}
