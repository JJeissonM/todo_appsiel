<?php

use App\Nomina\NomDocEncabezado;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NomDocEncabezadoOpcionesContabilizacionTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.nomina_contabilizacion_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        Config::set('database.default', 'nomina_contabilizacion_testing');
        DB::purge('nomina_contabilizacion_testing');

        Schema::create('core_tipos_docs_apps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefijo');
        });

        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('core_empresa_id')->nullable();
            $table->unsignedInteger('core_tipo_transaccion_id')->nullable();
            $table->unsignedInteger('core_tipo_doc_app_id');
            $table->string('consecutivo');
            $table->date('fecha');
            $table->string('descripcion');
            $table->string('estado');
        });

        Schema::create('contab_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('core_empresa_id');
            $table->unsignedInteger('core_tipo_transaccion_id');
            $table->unsignedInteger('core_tipo_doc_app_id');
            $table->string('consecutivo');
        });
    }

    public function tearDown()
    {
        DB::purge('nomina_contabilizacion_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function el_selector_de_contabilizacion_solo_muestra_documentos_activos()
    {
        DB::table('core_tipos_docs_apps')->insert([
            'id' => 1,
            'prefijo' => 'NM',
        ]);

        DB::table('nom_doc_encabezados')->insert([
            [
                'id' => 10,
                'core_tipo_doc_app_id' => 1,
                'consecutivo' => '79',
                'fecha' => '2026-06-30',
                'descripcion' => 'NÓMINA JUNIO',
                'estado' => NomDocEncabezado::ESTADO_ACTIVO,
            ],
            [
                'id' => 20,
                'core_tipo_doc_app_id' => 1,
                'consecutivo' => '78',
                'fecha' => '2026-05-31',
                'descripcion' => 'NÓMINA MAYO',
                'estado' => NomDocEncabezado::ESTADO_CONTABILIZADO,
            ],
            [
                'id' => 30,
                'core_tipo_doc_app_id' => 1,
                'consecutivo' => '77',
                'fecha' => '2026-04-30',
                'descripcion' => 'NÓMINA ABRIL',
                'estado' => 'Anulado',
            ],
        ]);

        $opciones = NomDocEncabezado::opciones_campo_select_contabilizacion();

        $this->assertSame(['', 'NM 79 - NÓMINA JUNIO'], array_values($opciones));
        $this->assertArrayHasKey(10, $opciones);
        $this->assertArrayNotHasKey(20, $opciones);
        $this->assertArrayNotHasKey(30, $opciones);
    }

    /** @test */
    public function excluye_documentos_activos_que_ya_tienen_movimientos_contables()
    {
        DB::table('core_tipos_docs_apps')->insert(['id' => 1, 'prefijo' => 'NM']);
        DB::table('nom_doc_encabezados')->insert([
            'id' => 40,
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 14,
            'core_tipo_doc_app_id' => 1,
            'consecutivo' => '80',
            'fecha' => '2026-07-31',
            'descripcion' => 'NÓMINA JULIO',
            'estado' => NomDocEncabezado::ESTADO_ACTIVO,
        ]);
        DB::table('contab_movimientos')->insert([
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 14,
            'core_tipo_doc_app_id' => 1,
            'consecutivo' => '80',
        ]);

        $this->assertArrayNotHasKey(40, NomDocEncabezado::opciones_campo_select_contabilizacion());
        $this->assertArrayNotHasKey(40, NomDocEncabezado::opciones_campo_select_ingreso_registros());
    }
}
