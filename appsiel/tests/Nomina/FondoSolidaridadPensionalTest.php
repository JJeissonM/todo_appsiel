<?php

use App\Nomina\ModosLiquidacion\Estrategias\FondoSolidaridadPensional;
use App\Nomina\ModosLiquidacion\LiquidacionConcepto;
use App\Http\Controllers\Nomina\NominaController;
use App\Nomina\NomContrato;
use App\Nomina\NomDocEncabezado;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FondoSolidaridadPensionalTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.fsp_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'fsp_testing');
        DB::purge('fsp_testing');

        $this->crearEsquema();
        $this->crearDatos();
    }

    public function tearDown()
    {
        DB::purge('fsp_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function liquida_el_fondo_a_partir_de_cuatro_smmlv()
    {
        $fsp = new FondoSolidaridadPensional();
        $this->assertEquals(
            5000000,
            $fsp->get_valor_neto_mes_completo(
                NomContrato::find(10),
                NomDocEncabezado::find(1)->lapso(),
                [1]
            )
        );
        $liquidacion = new LiquidacionConcepto(
            76,
            NomContrato::find(10),
            NomDocEncabezado::find(1)
        );

        $valores = $fsp->calcular($liquidacion);

        $this->assertEquals(0, $valores[0]['valor_devengo']);
        $this->assertEquals(50000, $valores[0]['valor_deduccion']);
    }

    /** @test */
    public function usa_la_agrupacion_de_pension_en_conceptos_migrados_sin_agrupacion()
    {
        $conceptoSolidaridad = DB::table('nom_conceptos')->where('id', 76)->first();
        $this->assertEquals(0, $conceptoSolidaridad->nom_agrupacion_id);

        $liquidacion = new LiquidacionConcepto(
            76,
            NomContrato::find(10),
            NomDocEncabezado::find(1)
        );
        $valores = (new FondoSolidaridadPensional())->calcular($liquidacion);

        $this->assertEquals(50000, $valores[0]['valor_deduccion']);
    }

    /** @test */
    public function no_liquida_a_contratistas_ni_pasantes()
    {
        DB::table('nom_contratos')->where('id', 10)->update(['clase_contrato' => 'labor_contratada']);
        $liquidacion = new LiquidacionConcepto(76, NomContrato::find(10), NomDocEncabezado::find(1));

        $valores = (new FondoSolidaridadPensional())->calcular($liquidacion);

        $this->assertEquals(0, $valores[0]['valor_deduccion']);
    }

    /** @test */
    public function aplica_las_tarifas_progresivas_configuradas()
    {
        $fsp = new FondoSolidaridadPensional();
        $smmlv = 1000000;

        $this->assertEquals(0, $fsp->calcular_valor_liquidacion_segun_tabla(3999999, 3999999, $smmlv));
        $this->assertEquals(40000, $fsp->calcular_valor_liquidacion_segun_tabla(4000000, 4000000, $smmlv));
        $this->assertEquals(192000, $fsp->calcular_valor_liquidacion_segun_tabla(16000000, 16000000, $smmlv));
        $this->assertEquals(238000, $fsp->calcular_valor_liquidacion_segun_tabla(17000000, 17000000, $smmlv));
        $this->assertEquals(288000, $fsp->calcular_valor_liquidacion_segun_tabla(18000000, 18000000, $smmlv));
        $this->assertEquals(342000, $fsp->calcular_valor_liquidacion_segun_tabla(19000000, 19000000, $smmlv));
        $this->assertEquals(400000, $fsp->calcular_valor_liquidacion_segun_tabla(20000000, 20000000, $smmlv));
    }

    /** @test */
    public function no_duplica_un_fondo_registrado_con_otro_concepto_del_mismo_codigo_dian()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 74, 'modo_liquidacion_id' => 2, 'naturaleza' => 'deduccion',
            'descripcion' => 'FONDO MANUAL', 'nom_agrupacion_id' => 0,
            'cpto_dian_id' => 36, 'estado' => 'Activo'
        ]);
        DB::table('nom_doc_registros')->insert([
            'id' => 2, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 50,
            'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 74,
            'fecha' => '2026-07-30', 'valor_devengo' => 0, 'valor_deduccion' => 50000
        ]);
        $usuario = new User(['email' => 'pruebas@appsiel.test']);

        (new NominaController())->liquidar_automaticos_empleado(
            10,
            NomContrato::find(10),
            NomDocEncabezado::find(1),
            $usuario
        );

        $this->assertSame(0, DB::table('nom_doc_registros')->where('nom_concepto_id', 76)->count());
        $this->assertSame(1, DB::table('nom_doc_registros')->where('nom_concepto_id', 74)->count());
    }

    protected function crearEsquema()
    {
        Schema::create('nom_parametros_legales', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->decimal('smmlv', 15, 2);
            $table->decimal('horas_laborales', 8, 3);
            $table->string('estado');
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
            $table->integer('modo_liquidacion_id');
            $table->string('naturaleza');
            $table->string('descripcion');
            $table->integer('nom_agrupacion_id')->default(0);
            $table->integer('cpto_dian_id')->nullable();
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->string('clase_contrato');
            $table->boolean('es_pasante_sena')->default(false);
            $table->string('tipo_cotizante')->nullable();
            $table->decimal('sueldo', 15, 2);
            $table->timestamps();
        });
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->decimal('tiempo_a_liquidar', 8, 3);
            $table->integer('core_empresa_id');
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('core_tercero_id');
            $table->integer('nom_contrato_id');
            $table->integer('core_empresa_id');
            $table->integer('nom_concepto_id');
            $table->integer('novedad_tnl_id')->nullable();
            $table->date('fecha');
            $table->decimal('cantidad_horas', 10, 2)->default(0);
            $table->decimal('valor_devengo', 15, 2)->default(0);
            $table->decimal('valor_deduccion', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('nom_elect_cat_cptos_dian', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo')->nullable();
        });
        Schema::create('nom_novedades_tnl', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    protected function crearDatos()
    {
        DB::table('nom_parametros_legales')->insert([
            'fecha_inicio' => '2026-01-01', 'fecha_fin' => null,
            'smmlv' => 1000000, 'horas_laborales' => 210, 'estado' => 'Activo'
        ]);
        DB::table('nom_agrupaciones_conceptos')->insert([
            'id' => 16, 'descripcion' => 'IBC PENSIÓN'
        ]);
        DB::table('nom_conceptos')->insert([
            ['id' => 1, 'modo_liquidacion_id' => 1, 'naturaleza' => 'devengo', 'descripcion' => 'SUELDO', 'nom_agrupacion_id' => 0, 'cpto_dian_id' => null, 'estado' => 'Activo'],
            ['id' => 73, 'modo_liquidacion_id' => 13, 'naturaleza' => 'deduccion', 'descripcion' => 'PENSIÓN', 'nom_agrupacion_id' => 16, 'cpto_dian_id' => null, 'estado' => 'Activo'],
            ['id' => 76, 'modo_liquidacion_id' => 10, 'naturaleza' => 'deduccion', 'descripcion' => 'FONDO SOLIDARIDAD', 'nom_agrupacion_id' => 0, 'cpto_dian_id' => 36, 'estado' => 'Activo'],
        ]);
        DB::table('nom_elect_cat_cptos_dian')->insert(['id' => 36, 'codigo' => 'FONDO_SOLIDARIDAD_PENSIONAL']);
        DB::table('nom_agrupacion_tiene_conceptos')->insert([
            'nom_agrupacion_id' => 16, 'nom_concepto_id' => 1
        ]);
        DB::table('nom_contratos')->insert([
            'id' => 10, 'core_tercero_id' => 50, 'clase_contrato' => 'normal',
            'es_pasante_sena' => 0, 'tipo_cotizante' => '01', 'sueldo' => 5000000
        ]);
        DB::table('nom_doc_encabezados')->insert([
            'id' => 1, 'fecha' => '2026-07-30', 'tiempo_a_liquidar' => 210,
            'core_empresa_id' => 1, 'estado' => 'Activo'
        ]);
        DB::table('nom_doc_registros')->insert([
            'id' => 1, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 50,
            'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 1,
            'fecha' => '2026-07-30', 'valor_devengo' => 5000000, 'valor_deduccion' => 0
        ]);
    }
}
