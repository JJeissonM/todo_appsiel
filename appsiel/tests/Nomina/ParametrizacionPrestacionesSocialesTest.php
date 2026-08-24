<?php

use App\Http\Controllers\Nomina\PrestacionesSocialesController;
use App\Nomina\ModosLiquidacion\Estrategias\PrestacionSocial;
use App\Nomina\NomContrato;
use App\Nomina\ParametroLiquidacionPrestacionesSociales;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParametrizacionPrestacionesSocialesTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.prestaciones_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'prestaciones_testing');
        DB::purge('prestaciones_testing');

        $this->crearEsquema();
        $this->crearDatos();
    }

    public function tearDown()
    {
        DB::purge('prestaciones_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function exige_agrupacion_cuando_la_base_usa_promedios()
    {
        $parametro = $this->parametro(['nom_agrupacion_id' => 0]);

        $this->assertContains('obligatoria', $parametro->validar_configuracion());
    }

    /** @test */
    public function rechaza_agrupaciones_inexistentes_o_sin_conceptos()
    {
        $this->assertContains(
            'no existe',
            $this->parametro(['nom_agrupacion_id' => 999])->validar_configuracion()
        );
        $this->assertContains(
            'no tiene conceptos asociados',
            $this->parametro(['nom_agrupacion_id' => 2])->validar_configuracion()
        );
    }

    /** @test */
    public function permite_sueldo_sin_agrupacion_y_promedio_con_agrupacion_valida()
    {
        $this->assertSame('', $this->parametro([
            'base_liquidacion' => 'sueldo',
            'nom_agrupacion_id' => 0
        ])->validar_configuracion());

        $this->assertSame('', $this->parametro([
            'nom_agrupacion_id' => 1
        ])->validar_configuracion());
    }

    /** @test */
    public function valida_la_agrupacion_especial_de_vacaciones_en_terminacion()
    {
        $parametro = $this->parametro([
            'concepto_prestacion' => 'vacaciones',
            'nom_agrupacion_id' => 1,
            'nom_agrupacion2_id' => 0
        ]);

        $this->assertContains('terminación de contrato', $parametro->validar_configuracion('terminacion_contrato'));
    }

    /** @test */
    public function la_validacion_previa_del_proceso_detecta_el_parametro_invalido()
    {
        DB::table('nom_parametros_liquidacion_prestaciones_sociales')->insert([
            'concepto_prestacion' => 'prima_legal',
            'grupo_empleado_id' => 1,
            'nom_agrupacion_id' => 0,
            'nom_agrupacion2_id' => 0,
            'nom_concepto_id' => 25,
            'base_liquidacion' => 'promedio_agrupacion'
        ]);

        $empleado = new NomContrato([
            'grupo_empleado_id' => 1,
            'clase_contrato' => 'normal',
            'tipo_cotizante' => '01'
        ]);
        $errores = (new PrestacionesSocialesControllerPrueba())->validar(
            collect([$empleado]),
            ['prima_legal'],
            'terminacion_contrato'
        );

        $this->assertCount(1, $errores);
        $this->assertContains('agrupación de conceptos', $errores[0]);
    }

    /** @test */
    public function el_calculo_compartido_rechaza_una_agrupacion_invalida_con_mensaje_controlado()
    {
        $this->setExpectedException(
            InvalidArgumentException::class,
            'La agrupación de conceptos de la prestación no existe o no fue configurada.'
        );

        PrestacionSocial::get_agrupacion_validada(0);
    }

    protected function parametro(array $cambios)
    {
        return new ParametroLiquidacionPrestacionesSociales(array_merge([
            'concepto_prestacion' => 'prima_legal',
            'grupo_empleado_id' => 1,
            'nom_agrupacion_id' => 1,
            'nom_agrupacion2_id' => 0,
            'nom_concepto_id' => 25,
            'base_liquidacion' => 'promedio_agrupacion'
        ], $cambios));
    }

    protected function crearEsquema()
    {
        Schema::create('nom_grupos_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_agrupaciones_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('nom_agrupacion_tiene_conceptos', function (Blueprint $table) {
            $table->integer('nom_agrupacion_id');
            $table->integer('nom_concepto_id');
        });
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->timestamps();
        });
        Schema::create('nom_parametros_liquidacion_prestaciones_sociales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concepto_prestacion');
            $table->integer('grupo_empleado_id');
            $table->integer('nom_agrupacion_id')->default(0);
            $table->integer('nom_agrupacion2_id')->default(0);
            $table->integer('nom_concepto_id')->default(0);
            $table->string('base_liquidacion');
            $table->timestamps();
        });
    }

    protected function crearDatos()
    {
        DB::table('nom_grupos_empleados')->insert(['id' => 1, 'descripcion' => 'RAIZ']);
        DB::table('nom_agrupaciones_conceptos')->insert([
            ['id' => 1, 'descripcion' => 'LIQUIDACIÓN PRIMA'],
            ['id' => 2, 'descripcion' => 'AGRUPACIÓN VACÍA']
        ]);
        DB::table('nom_conceptos')->insert(['id' => 25, 'descripcion' => 'PRIMA DE SERVICIOS']);
        DB::table('nom_agrupacion_tiene_conceptos')->insert([
            'nom_agrupacion_id' => 1,
            'nom_concepto_id' => 25
        ]);
    }
}

class PrestacionesSocialesControllerPrueba extends PrestacionesSocialesController
{
    public function validar($empleados, array $prestaciones, $tipoLiquidacion)
    {
        return $this->validar_parametrizacion($empleados, $prestaciones, $tipoLiquidacion);
    }
}
