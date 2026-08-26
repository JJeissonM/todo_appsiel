<?php

use App\Nomina\NomDocRegistro;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NomDocRegistroCuotaCrudTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.cuota_crud_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'cuota_crud_testing');
        DB::purge('cuota_crud_testing');

        $this->crearEsquema();
        $this->crearDatos();
    }

    public function tearDown()
    {
        DB::purge('cuota_crud_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function solo_habilita_como_excepcion_los_registros_con_cuota_asociada()
    {
        $this->assertTrue(NomDocRegistro::find(1)->es_cuota_editable_desde_crud());
        $this->assertFalse(NomDocRegistro::find(3)->es_cuota_editable_desde_crud());
        $this->assertFalse(NomDocRegistro::find(4)->es_cuota_editable_desde_crud());
    }

    /** @test */
    public function conserva_las_asociaciones_al_preparar_la_actualizacion_generica()
    {
        $request = Request::create('/', 'POST', [
            'nom_doc_encabezado_id' => 999,
            'nom_contrato_id' => 999,
            'nom_concepto_id' => 4,
            'nom_cuota_id' => 999,
            'valor_deduccion' => 40
        ]);

        (new NomDocRegistro())->preparar_request_validacion($request, 1);

        $this->assertEquals(1, $request->nom_doc_encabezado_id);
        $this->assertEquals(10, $request->nom_contrato_id);
        $this->assertEquals(3, $request->nom_concepto_id);
        $this->assertEquals(5, $request->nom_cuota_id);
    }

    /** @test */
    public function al_modificar_una_cuota_sincroniza_acumulado_estado_y_totales_del_documento()
    {
        $modelo = new NomDocRegistro();
        $modelo->preparar_request_validacion(Request::create('/', 'POST'), 1);
        $registro = NomDocRegistro::find(1);
        $registro->valor_deduccion = 40;
        $registro->save();

        $modelo->update_adicional([
            'valor_devengo' => 0,
            'valor_deduccion' => 40,
            'cantidad_horas' => 0
        ], 1);

        $cuota = DB::table('nom_cuotas')->where('id', 5)->first();
        $documento = DB::table('nom_doc_encabezados')->where('id', 1)->first();

        $this->assertEquals(80, $cuota->valor_acumulado);
        $this->assertEquals('Activo', $cuota->estado);
        $this->assertEquals(100, $documento->total_deducciones);
        $this->assertEquals(0, $documento->total_devengos);
    }

    /** @test */
    public function al_dejar_en_cero_elimina_el_registro_y_conserva_los_acumulados_consistentes()
    {
        $modelo = new NomDocRegistro();
        $modelo->preparar_request_validacion(Request::create('/', 'POST'), 1);
        $registro = NomDocRegistro::find(1);
        $registro->valor_deduccion = 0;
        $registro->save();

        $modelo->update_adicional([
            'valor_devengo' => 0,
            'valor_deduccion' => 0,
            'cantidad_horas' => 0
        ], 1);

        $this->assertNull(NomDocRegistro::find(1));
        $this->assertEquals(40, DB::table('nom_cuotas')->where('id', 5)->value('valor_acumulado'));
        $this->assertEquals(60, DB::table('nom_doc_encabezados')->where('id', 1)->value('total_deducciones'));
    }

    /** @test */
    public function ajusta_por_diferencia_sin_borrar_saldos_iniciales_de_la_cuota()
    {
        DB::table('nom_cuotas')->where('id', 5)->update([
            'valor_acumulado' => 150,
            'tope_maximo' => 200,
            'estado' => 'Activo'
        ]);

        $modelo = new NomDocRegistro();
        $modelo->preparar_request_validacion(Request::create('/', 'POST'), 1);
        $registro = NomDocRegistro::find(1);
        $registro->valor_deduccion = 40;
        $registro->save();

        $modelo->update_adicional([
            'valor_devengo' => 0,
            'valor_deduccion' => 40,
            'cantidad_horas' => 0
        ], 1);

        $this->assertEquals(130, DB::table('nom_cuotas')->where('id', 5)->value('valor_acumulado'));
    }

    protected function crearEsquema()
    {
        Schema::create('nom_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modo_liquidacion_id');
            $table->string('naturaleza');
            $table->string('descripcion');
            $table->timestamps();
        });
        Schema::create('nom_cuotas', function (Blueprint $table) {
            $table->increments('id');
            $table->decimal('valor_acumulado', 15, 2)->default(0);
            $table->decimal('tope_maximo', 15, 2)->nullable();
            $table->string('estado');
            $table->timestamps();
        });
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('estado');
            $table->decimal('total_devengos', 15, 2)->default(0);
            $table->decimal('total_deducciones', 15, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id');
            $table->integer('core_tercero_id')->nullable();
            $table->integer('nom_contrato_id')->nullable();
            $table->integer('core_empresa_id')->nullable();
            $table->integer('nom_concepto_id');
            $table->integer('nom_cuota_id')->nullable();
            $table->integer('nom_prestamo_id')->nullable();
            $table->integer('novedad_tnl_id')->nullable();
            $table->integer('orden_trabajo_id')->nullable();
            $table->decimal('cantidad_horas', 10, 2)->default(0);
            $table->decimal('valor_devengo', 15, 2)->default(0);
            $table->decimal('valor_deduccion', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    protected function crearDatos()
    {
        DB::table('nom_conceptos')->insert([
            ['id' => 3, 'modo_liquidacion_id' => 3, 'naturaleza' => 'deduccion', 'descripcion' => 'CUOTA'],
            ['id' => 4, 'modo_liquidacion_id' => 4, 'naturaleza' => 'deduccion', 'descripcion' => 'PRESTAMO']
        ]);
        DB::table('nom_cuotas')->insert([
            'id' => 5,
            'valor_acumulado' => 100,
            'tope_maximo' => 100,
            'estado' => 'Inactivo'
        ]);
        DB::table('nom_doc_encabezados')->insert([
            'id' => 1,
            'estado' => 'Activo',
            'total_devengos' => 0,
            'total_deducciones' => 100
        ]);
        DB::table('nom_doc_registros')->insert([
            [
                'id' => 1, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 20,
                'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 3,
                'nom_cuota_id' => 5, 'cantidad_horas' => 0, 'valor_devengo' => 0,
                'valor_deduccion' => 60
            ],
            [
                'id' => 2, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 20,
                'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 3,
                'nom_cuota_id' => 5, 'cantidad_horas' => 0, 'valor_devengo' => 0,
                'valor_deduccion' => 40
            ],
            [
                'id' => 3, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 20,
                'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 4,
                'nom_cuota_id' => null, 'cantidad_horas' => 0, 'valor_devengo' => 0,
                'valor_deduccion' => 10
            ],
            [
                'id' => 4, 'nom_doc_encabezado_id' => 1, 'core_tercero_id' => 20,
                'nom_contrato_id' => 10, 'core_empresa_id' => 1, 'nom_concepto_id' => 3,
                'nom_cuota_id' => null, 'cantidad_horas' => 0, 'valor_devengo' => 0,
                'valor_deduccion' => 10
            ]
        ]);
    }
}
