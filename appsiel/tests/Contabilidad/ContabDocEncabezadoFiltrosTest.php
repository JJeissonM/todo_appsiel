<?php

use App\Contabilidad\ContabDocEncabezado;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContabDocEncabezadoFiltrosTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();

        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.contab_documentos_filtros_testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ]);
        Config::set('database.default', 'contab_documentos_filtros_testing');
        DB::purge('contab_documentos_filtros_testing');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function () {
            return implode('', func_get_args());
        });

        $this->crearEsquema();
        $this->crearDatos();

        $usuario = new User(['empresa_id' => 1, 'email' => 'pruebas@appsiel.test']);
        $usuario->id = 99;
        Auth::setUser($usuario);
    }

    public function tearDown()
    {
        DB::purge('contab_documentos_filtros_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    /** @test */
    public function configura_los_tipos_de_control_solicitados()
    {
        $filtros = ContabDocEncabezado::get_filtros_avanzados_index();

        $this->assertSame('combobox', $filtros['filtro_tipo_documento']['type']);
        $this->assertSame('text', $filtros['filtro_consecutivo']['type']);
        $this->assertSame('text', $filtros['filtro_fecha']['type']);
        $this->assertSame('combobox', $filtros['filtro_tercero']['type']);
        $this->assertSame('select', $filtros['filtro_estado']['type']);
        $this->assertSame('RC - RECIBO DE CAJA', $filtros['filtro_tipo_documento']['options'][10]);
        $this->assertSame('CLIENTE UNO (1001)', $filtros['filtro_tercero']['options'][100]);
        $this->assertArrayNotHasKey(200, $filtros['filtro_tercero']['options']);
    }

    /** @test */
    public function fecha_y_consecutivo_se_buscan_por_coincidencia_parcial()
    {
        request()->replace([
            'filtro_fecha' => '2026-09',
            'filtro_consecutivo' => '001'
        ]);

        $registros = ContabDocEncabezado::consultar_registros(10, '');

        $this->assertSame(1, $registros->total());
        $this->assertSame(1, (int)$registros->first()->campo7);
    }

    /** @test */
    public function combina_los_filtros_de_campos_del_documento()
    {
        request()->replace([
            'filtro_tipo_documento' => 20,
            'filtro_empresa' => 1,
            'filtro_tercero' => 101,
            'filtro_documento_soporte' => 'FAC-88',
            'filtro_descripcion' => 'servicio',
            'filtro_valor_total' => '250000',
            'filtro_estado' => 'Anulado'
        ]);

        $registros = ContabDocEncabezado::consultar_registros(10, '');
        $exportados = DB::select(ContabDocEncabezado::sqlString(''));

        $this->assertSame(1, $registros->total());
        $this->assertSame(2, (int)$registros->first()->campo7);
        $this->assertCount(1, $exportados);
        $this->assertSame('Anulado', $exportados[0]->ESTADO);
    }

    /** @test */
    public function la_busqueda_global_no_omite_el_aislamiento_por_empresa()
    {
        request()->replace([]);

        $registros = ContabDocEncabezado::consultar_registros(10, 'DOCUMENTO EXTERNO');

        $this->assertSame(0, $registros->total());
    }

    /** @test */
    public function la_consulta_de_impresion_puede_restringirse_a_la_empresa_actual()
    {
        $this->assertNotNull(ContabDocEncabezado::get_registro_impresion(1, 1));
        $this->assertNull(ContabDocEncabezado::get_registro_impresion(3, 1));
        $this->assertNull(ContabDocEncabezado::get_registro_impresion(999, 1));
    }

    /** @test */
    public function show_redirige_si_el_documento_no_pertenece_a_la_empresa_actual()
    {
        request()->replace([
            'id' => 14,
            'id_modelo' => 47,
            'id_transaccion' => 9
        ]);

        $response = (new ContabilidadController())->show(3);

        $this->assertInstanceOf('Illuminate\\Http\\RedirectResponse', $response);
        $this->assertSame(
            url('web?id=14&id_modelo=47&id_transaccion=9'),
            $response->getTargetUrl()
        );
        $this->assertSame(
            'El documento contable no existe o no pertenece a la empresa actual.',
            $response->getSession()->get('mensaje_error')
        );
    }

    protected function crearEsquema()
    {
        Schema::create('core_tipos_docs_apps', function (Blueprint $table) {
            $table->increments('id');
            $table->string('prefijo');
            $table->string('descripcion');
        });
        Schema::create('core_empresas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
        });
        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });
        Schema::create('contab_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tipo_transaccion_id')->default(9);
            $table->integer('core_tipo_doc_app_id');
            $table->string('consecutivo');
            $table->string('fecha');
            $table->integer('core_empresa_id');
            $table->integer('core_tercero_id');
            $table->string('documento_soporte')->nullable();
            $table->string('descripcion');
            $table->decimal('valor_total', 15, 2);
            $table->string('estado');
            $table->string('creado_por')->nullable();
            $table->timestamps();
        });
    }

    protected function crearDatos()
    {
        DB::table('core_tipos_docs_apps')->insert([
            ['id' => 10, 'prefijo' => 'RC', 'descripcion' => 'RECIBO DE CAJA'],
            ['id' => 20, 'prefijo' => 'NC', 'descripcion' => 'NOTA CONTABLE']
        ]);
        DB::table('core_empresas')->insert([
            ['id' => 1, 'descripcion' => 'EMPRESA UNO'],
            ['id' => 2, 'descripcion' => 'EMPRESA DOS']
        ]);
        DB::table('core_terceros')->insert([
            ['id' => 100, 'core_empresa_id' => 1, 'numero_identificacion' => '1001', 'descripcion' => 'CLIENTE UNO'],
            ['id' => 101, 'core_empresa_id' => 1, 'numero_identificacion' => '1002', 'descripcion' => 'CLIENTE DOS'],
            ['id' => 200, 'core_empresa_id' => 2, 'numero_identificacion' => '2001', 'descripcion' => 'CLIENTE EXTERNO']
        ]);
        DB::table('contab_doc_encabezados')->insert([
            $this->documento(1, 10, 'RC-001', '2026-09-01', 1, 100, 'FAC-11', 'INGRESO CAJA', 100000, 'Activo'),
            $this->documento(2, 20, 'NC-002', '2026-08-31', 1, 101, 'FAC-88', 'AJUSTE SERVICIO', 250000, 'Anulado'),
            $this->documento(3, 10, 'RC-001', '2026-09-02', 2, 200, 'EXT-1', 'DOCUMENTO EXTERNO', 500000, 'Activo')
        ]);
    }

    protected function documento($id, $tipo, $consecutivo, $fecha, $empresa, $tercero, $soporte, $descripcion, $valor, $estado)
    {
        return [
            'id' => $id,
            'core_tipo_doc_app_id' => $tipo,
            'consecutivo' => $consecutivo,
            'fecha' => $fecha,
            'core_empresa_id' => $empresa,
            'core_tercero_id' => $tercero,
            'documento_soporte' => $soporte,
            'descripcion' => $descripcion,
            'valor_total' => $valor,
            'estado' => $estado,
            'created_at' => '2026-09-01 00:00:00',
            'updated_at' => '2026-09-01 00:00:00'
        ];
    }
}
