<?php

use App\Nomina\NomDocEncabezado;
use App\Nomina\Services\RetiroPersonalizadoNominaService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RetiroPersonalizadoNominaServiceTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.retiro_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'retiro_testing');
        DB::purge('retiro_testing');

        $this->crearEsquema();
        $this->crearDatosBase();
    }

    public function tearDown()
    {
        DB::purge('retiro_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function retira_un_concepto_manual_y_actualiza_los_totales_del_documento()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 20, 'modo_liquidacion_id' => 2, 'naturaleza' => 'devengo', 'descripcion' => 'BONIFICACIÓN'
        ]);
        $this->insertarRegistroNomina([
            'id' => 100, 'nom_concepto_id' => 20, 'valor_devengo' => 150000, 'valor_deduccion' => 0
        ]);

        $resultado = (new RetiroPersonalizadoNominaService())->retirar(
            NomDocEncabezado::find(1), 5, 10, 20
        );

        $this->assertSame(1, $resultado['cantidad_retirada']);
        $this->assertSame(0, DB::table('nom_doc_registros')->count());
        $this->assertEquals(0, DB::table('nom_doc_encabezados')->where('id', 1)->value('total_devengos'));
    }

    /** @test */
    public function una_cuota_inactiva_por_decision_del_usuario_no_se_reactiva_al_retirar()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 30, 'modo_liquidacion_id' => 3, 'naturaleza' => 'deduccion', 'descripcion' => 'CUOTA'
        ]);
        DB::table('nom_cuotas')->insert([
            'id' => 7, 'tope_maximo' => 200000, 'valor_acumulado' => 100000, 'estado' => 'Inactivo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 101, 'nom_concepto_id' => 30, 'nom_cuota_id' => 7,
            'valor_devengo' => 0, 'valor_deduccion' => 40000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 30);

        $cuota = DB::table('nom_cuotas')->where('id', 7)->first();
        $this->assertEquals(60000, $cuota->valor_acumulado);
        $this->assertSame('Inactivo', $cuota->estado);
        $this->assertSame(0, DB::table('nom_doc_registros')->count());
    }

    /** @test */
    public function reactiva_una_cuota_inactivada_al_alcanzar_el_tope_de_la_liquidacion_retirada()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 31, 'modo_liquidacion_id' => 3, 'naturaleza' => 'deduccion', 'descripcion' => 'CUOTA CON TOPE'
        ]);
        DB::table('nom_cuotas')->insert([
            'id' => 9, 'tope_maximo' => 100000, 'valor_acumulado' => 100000, 'estado' => 'Inactivo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 107, 'nom_concepto_id' => 31, 'nom_cuota_id' => 9,
            'valor_devengo' => 0, 'valor_deduccion' => 40000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 31);

        $cuota = DB::table('nom_cuotas')->where('id', 9)->first();
        $this->assertEquals(60000, $cuota->valor_acumulado);
        $this->assertSame('Activo', $cuota->estado);
    }

    /** @test */
    public function conserva_activa_una_cuota_que_estaba_activa_antes_del_retiro()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 32, 'modo_liquidacion_id' => 3, 'naturaleza' => 'deduccion', 'descripcion' => 'CUOTA ACTIVA'
        ]);
        DB::table('nom_cuotas')->insert([
            'id' => 11, 'tope_maximo' => 200000, 'valor_acumulado' => 100000, 'estado' => 'Activo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 109, 'nom_concepto_id' => 32, 'nom_cuota_id' => 11,
            'valor_devengo' => 0, 'valor_deduccion' => 40000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 32);

        $cuota = DB::table('nom_cuotas')->where('id', 11)->first();
        $this->assertEquals(60000, $cuota->valor_acumulado);
        $this->assertSame('Activo', $cuota->estado);
    }

    /** @test */
    public function una_asociacion_inconsistente_revierte_toda_la_operacion()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 30, 'modo_liquidacion_id' => 3, 'naturaleza' => 'deduccion', 'descripcion' => 'CUOTA'
        ]);
        $this->insertarRegistroNomina([
            'id' => 102, 'nom_concepto_id' => 30, 'nom_cuota_id' => 999,
            'valor_devengo' => 0, 'valor_deduccion' => 40000
        ]);

        try {
            (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 30);
            $this->fail('Se esperaba una excepción por la cuota inexistente.');
        } catch (RuntimeException $e) {
            $this->assertSame(1, DB::table('nom_doc_registros')->count());
            $this->assertEquals(150000, DB::table('nom_doc_encabezados')->where('id', 1)->value('total_devengos'));
        }
    }

    /** @test */
    public function un_prestamo_inactivo_por_decision_del_usuario_no_se_reactiva_al_retirar()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 40, 'modo_liquidacion_id' => 4, 'naturaleza' => 'deduccion', 'descripcion' => 'PRÉSTAMO'
        ]);
        DB::table('nom_prestamos')->insert([
            'id' => 8, 'valor_prestamo' => 500000, 'valor_acumulado' => 250000, 'estado' => 'Inactivo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 103, 'nom_concepto_id' => 40, 'nom_prestamo_id' => 8,
            'valor_devengo' => 0, 'valor_deduccion' => 50000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 40, 1);

        $prestamo = DB::table('nom_prestamos')->where('id', 8)->first();
        $this->assertEquals(200000, $prestamo->valor_acumulado);
        $this->assertSame('Inactivo', $prestamo->estado);
    }

    /** @test */
    public function reactiva_un_prestamo_inactivado_al_alcanzar_el_valor_total_en_la_liquidacion_retirada()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 41, 'modo_liquidacion_id' => 4, 'naturaleza' => 'deduccion', 'descripcion' => 'PRÉSTAMO COMPLETO'
        ]);
        DB::table('nom_prestamos')->insert([
            'id' => 10, 'valor_prestamo' => 250000, 'valor_acumulado' => 250000, 'estado' => 'Inactivo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 108, 'nom_concepto_id' => 41, 'nom_prestamo_id' => 10,
            'valor_devengo' => 0, 'valor_deduccion' => 50000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 41, 1);

        $prestamo = DB::table('nom_prestamos')->where('id', 10)->first();
        $this->assertEquals(200000, $prestamo->valor_acumulado);
        $this->assertSame('Activo', $prestamo->estado);
    }

    /** @test */
    public function conserva_activo_un_prestamo_que_estaba_activo_antes_del_retiro()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 42, 'modo_liquidacion_id' => 4, 'naturaleza' => 'deduccion', 'descripcion' => 'PRÉSTAMO ACTIVO'
        ]);
        DB::table('nom_prestamos')->insert([
            'id' => 12, 'valor_prestamo' => 500000, 'valor_acumulado' => 250000, 'estado' => 'Activo'
        ]);
        $this->insertarRegistroNomina([
            'id' => 113, 'nom_concepto_id' => 42, 'nom_prestamo_id' => 12,
            'valor_devengo' => 0, 'valor_deduccion' => 50000
        ]);

        (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), 5, 10, 42, 1);

        $prestamo = DB::table('nom_prestamos')->where('id', 12)->first();
        $this->assertEquals(200000, $prestamo->valor_acumulado);
        $this->assertSame('Activo', $prestamo->estado);
    }

    /** @test */
    public function las_opciones_solo_incluyen_combinaciones_existentes_en_el_documento()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 20, 'modo_liquidacion_id' => 2, 'naturaleza' => 'devengo', 'descripcion' => 'BONIFICACIÓN'
        ]);
        $this->insertarRegistroNomina([
            'id' => 104, 'nom_concepto_id' => 20, 'valor_devengo' => 150000, 'valor_deduccion' => 0
        ]);

        $opciones = (new RetiroPersonalizadoNominaService())->opciones(NomDocEncabezado::find(1));

        $this->assertCount(1, $opciones);
        $this->assertSame(5, $opciones[0]['grupo_id']);
        $this->assertSame(10, $opciones[0]['contrato_id']);
        $this->assertSame(20, $opciones[0]['concepto_id']);
        $this->assertSame(1, $opciones[0]['cantidad']);
    }

    /** @test */
    public function exige_al_menos_un_filtro_para_evitar_retirar_todo_el_documento()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 20, 'modo_liquidacion_id' => 2, 'naturaleza' => 'devengo', 'descripcion' => 'BONIFICACIÓN'
        ]);
        $this->insertarRegistroNomina([
            'id' => 105, 'nom_concepto_id' => 20, 'valor_devengo' => 150000, 'valor_deduccion' => 0
        ]);

        try {
            (new RetiroPersonalizadoNominaService())->retirar(NomDocEncabezado::find(1), null, null, null);
            $this->fail('Se esperaba una excepción por no seleccionar filtros.');
        } catch (RuntimeException $e) {
            $this->assertSame(1, DB::table('nom_doc_registros')->count());
        }
    }

    /** @test */
    public function ignora_identificadores_cero_de_asociaciones_en_registros_migrados()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 20, 'modo_liquidacion_id' => 2, 'naturaleza' => 'deduccion', 'descripcion' => 'DESCUENTO MIGRADO'
        ]);
        $this->insertarRegistroNomina([
            'id' => 106,
            'nom_concepto_id' => 20,
            'nom_cuota_id' => 0,
            'nom_prestamo_id' => 0,
            'novedad_tnl_id' => 0,
            'orden_trabajo_id' => 0,
            'valor_devengo' => 0,
            'valor_deduccion' => 50000,
        ]);

        $resultado = (new RetiroPersonalizadoNominaService())->retirar(
            NomDocEncabezado::find(1), null, 10, null, 1
        );

        $this->assertSame(1, $resultado['cantidad_retirada']);
        $this->assertSame(0, DB::table('nom_doc_registros')->count());
    }

    /** @test */
    public function permite_retirar_todos_los_registros_de_un_empleado_sin_grupo_ni_concepto()
    {
        $this->crearConceptosManuales();
        $this->crearEmpleado(11, 51, 5, '456', 'OTRO EMPLEADO');
        $this->insertarRegistroNomina(['id' => 110, 'nom_concepto_id' => 20, 'valor_devengo' => 100, 'valor_deduccion' => 0]);
        $this->insertarRegistroNomina(['id' => 111, 'nom_concepto_id' => 21, 'valor_devengo' => 200, 'valor_deduccion' => 0]);
        $this->insertarRegistroNomina([
            'id' => 112, 'nom_contrato_id' => 11, 'core_tercero_id' => 51,
            'nom_concepto_id' => 20, 'valor_devengo' => 300, 'valor_deduccion' => 0
        ]);

        $resultado = (new RetiroPersonalizadoNominaService())->retirar(
            NomDocEncabezado::find(1), null, 10, null, 2
        );

        $this->assertSame(2, $resultado['cantidad_retirada']);
        $this->assertEquals([112], DB::table('nom_doc_registros')->lists('id'));
    }

    /** @test */
    public function permite_retirar_un_concepto_para_todos_los_empleados()
    {
        $this->crearConceptosManuales();
        $this->crearEmpleado(11, 51, 5, '456', 'OTRO EMPLEADO');
        $this->insertarRegistroNomina(['id' => 120, 'nom_concepto_id' => 20, 'valor_devengo' => 100, 'valor_deduccion' => 0]);
        $this->insertarRegistroNomina(['id' => 121, 'nom_concepto_id' => 21, 'valor_devengo' => 200, 'valor_deduccion' => 0]);
        $this->insertarRegistroNomina([
            'id' => 122, 'nom_contrato_id' => 11, 'core_tercero_id' => 51,
            'nom_concepto_id' => 20, 'valor_devengo' => 300, 'valor_deduccion' => 0
        ]);

        $resultado = (new RetiroPersonalizadoNominaService())->retirar(
            NomDocEncabezado::find(1), null, null, 20, 2
        );

        $this->assertSame(2, $resultado['cantidad_retirada']);
        $this->assertEquals([121], DB::table('nom_doc_registros')->lists('id'));
    }

    /** @test */
    public function permite_retirar_todos_los_registros_de_un_grupo()
    {
        $this->crearConceptosManuales();
        $this->crearEmpleado(11, 51, 5, '456', 'EMPLEADO MISMO GRUPO');
        $this->crearEmpleado(12, 52, 6, '789', 'EMPLEADO OTRO GRUPO');
        DB::table('nom_grupos_empleados')->insert(['id' => 6, 'descripcion' => 'DOCENTES']);
        $this->insertarRegistroNomina(['id' => 130, 'nom_concepto_id' => 20, 'valor_devengo' => 100, 'valor_deduccion' => 0]);
        $this->insertarRegistroNomina([
            'id' => 131, 'nom_contrato_id' => 11, 'core_tercero_id' => 51,
            'nom_concepto_id' => 21, 'valor_devengo' => 200, 'valor_deduccion' => 0
        ]);
        $this->insertarRegistroNomina([
            'id' => 132, 'nom_contrato_id' => 12, 'core_tercero_id' => 52,
            'nom_concepto_id' => 20, 'valor_devengo' => 300, 'valor_deduccion' => 0
        ]);

        $resultado = (new RetiroPersonalizadoNominaService())->retirar(
            NomDocEncabezado::find(1), 5, null, null, 2
        );

        $this->assertSame(2, $resultado['cantidad_retirada']);
        $this->assertEquals([132], DB::table('nom_doc_registros')->lists('id'));
    }

    /** @test */
    public function los_calculos_del_documento_consultan_registros_creados_despues_de_cargar_la_relacion()
    {
        DB::table('nom_conceptos')->insert([
            'id' => 22, 'modo_liquidacion_id' => 1, 'naturaleza' => 'devengo', 'descripcion' => 'SUELDO'
        ]);

        $documento = NomDocEncabezado::find(1);
        $this->assertCount(0, $documento->registros_liquidacion);

        $this->insertarRegistroNomina([
            'id' => 140,
            'nom_concepto_id' => 22,
            'cantidad_horas' => 210,
            'valor_devengo' => 1750905,
            'valor_deduccion' => 0,
        ]);

        $this->assertEquals(210, $documento->horas_liquidadas_empleado(50));
        $this->assertEquals(210, $documento->horas_liquidadas_tiempo_laborado_empleado(50));
        $this->assertEquals(1750905, $documento->get_valor_neto_empleado_concepto(50, 22));
        $this->assertEquals(1750905, $documento->get_valor_neto_empleado_segun_grupo_conceptos([22], 50));
    }

    protected function crearEsquema()
    {
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('tipo_liquidacion')->default('normal');
            $table->decimal('total_devengos', 15, 2)->default(0);
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->string('estado');
            $table->timestamps();
        });

        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id');
            $table->integer('grupo_empleado_id')->nullable();
            $table->string('clase_contrato')->default('normal');
            $table->timestamps();
        });

        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });

        Schema::create('nom_grupos_empleados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });

        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modo_liquidacion_id');
            $table->string('naturaleza');
            $table->string('descripcion');
            $table->timestamps();
        });

        Schema::create('nom_cuotas', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('tope_maximo', 15, 2)->nullable();
            $table->decimal('valor_acumulado', 15, 2)->default(0);
            $table->string('estado');
            $table->timestamps();
        });

        Schema::create('nom_prestamos', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('valor_prestamo', 15, 2)->default(0);
            $table->decimal('valor_acumulado', 15, 2)->default(0);
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
            $table->integer('nom_cuota_id')->nullable();
            $table->integer('nom_prestamo_id')->nullable();
            $table->integer('novedad_tnl_id')->nullable();
            $table->integer('orden_trabajo_id')->nullable();
            $table->decimal('cantidad_horas', 15, 2)->default(0);
            $table->decimal('valor_devengo', 15, 2)->default(0);
            $table->decimal('valor_deduccion', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    protected function crearDatosBase()
    {
        DB::table('nom_doc_encabezados')->insert([
            'id' => 1,
            'core_empresa_id' => 99,
            'tipo_liquidacion' => 'normal',
            'total_devengos' => 150000,
            'total_deducciones' => 0,
            'estado' => 'Activo',
        ]);
        DB::table('nom_contratos')->insert([
            'id' => 10, 'core_tercero_id' => 50, 'grupo_empleado_id' => 5, 'clase_contrato' => 'normal'
        ]);
        DB::table('core_terceros')->insert([
            'id' => 50, 'numero_identificacion' => '123', 'descripcion' => 'EMPLEADA PRUEBA'
        ]);
        DB::table('nom_grupos_empleados')->insert([
            'id' => 5, 'descripcion' => 'ADMINISTRATIVOS'
        ]);
    }

    protected function crearConceptosManuales()
    {
        DB::table('nom_conceptos')->insert([
            ['id' => 20, 'modo_liquidacion_id' => 2, 'naturaleza' => 'devengo', 'descripcion' => 'BONIFICACIÓN'],
            ['id' => 21, 'modo_liquidacion_id' => 2, 'naturaleza' => 'deduccion', 'descripcion' => 'DESCUENTO MANUAL'],
        ]);
    }

    protected function crearEmpleado($contratoId, $terceroId, $grupoId, $identificacion, $nombre)
    {
        DB::table('core_terceros')->insert([
            'id' => $terceroId, 'numero_identificacion' => $identificacion, 'descripcion' => $nombre
        ]);
        DB::table('nom_contratos')->insert([
            'id' => $contratoId, 'core_tercero_id' => $terceroId,
            'grupo_empleado_id' => $grupoId, 'clase_contrato' => 'normal'
        ]);
    }

    protected function insertarRegistroNomina(array $datos)
    {
        DB::table('nom_doc_registros')->insert($datos + [
            'nom_doc_encabezado_id' => 1,
            'core_tercero_id' => 50,
            'nom_contrato_id' => 10,
            'core_empresa_id' => 99,
            'nom_cuota_id' => null,
            'nom_prestamo_id' => null,
            'novedad_tnl_id' => null,
            'orden_trabajo_id' => null,
            'cantidad_horas' => 0,
        ]);
    }
}
