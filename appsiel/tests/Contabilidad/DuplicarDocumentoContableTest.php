<?php

use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DuplicarDocumentoContableTest extends TestCase
{
    protected $conexionOriginal;

    public function setUp()
    {
        parent::setUp();
        $this->conexionOriginal = Config::get('database.default');
        Config::set('database.connections.duplicar_contable_testing', [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''
        ]);
        Config::set('database.default', 'duplicar_contable_testing');
        DB::purge('duplicar_contable_testing');

        Schema::create('core_tipos_docs_apps', function (Blueprint $table) {
            $table->increments('id');
        });
        Schema::create('core_consecutivos_documentos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_documento_app_id');
            $table->integer('consecutivo_actual');
            $table->timestamps();
        });
        Schema::create('contab_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_tipo_doc_app_id');
            $table->integer('consecutivo');
            $table->string('descripcion');
            $table->string('estado');
            $table->string('creado_por');
            $table->string('modificado_por');
            $table->timestamps();
        });
        Schema::create('contab_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contab_doc_encabezado_id');
            $table->string('tipo_transaccion');
            $table->decimal('valor_debito', 15, 2);
            $table->decimal('valor_credito', 15, 2);
            $table->string('estado');
            $table->timestamps();
        });
        DB::table('core_tipos_docs_apps')->insert(['id' => 1]);
        DB::table('core_consecutivos_documentos')->insert([
            'core_empresa_id' => 1, 'core_documento_app_id' => 1, 'consecutivo_actual' => 1895
        ]);
        $usuario = new User(['empresa_id' => 1, 'email' => 'actual@appsiel.test']);
        $usuario->id = 99;
        Auth::setUser($usuario);
    }

    public function tearDown()
    {
        DB::purge('duplicar_contable_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    public function test_duplica_documentos_activos_y_anulados_con_lineas_activas()
    {
        foreach (['Activo', 'Anulado'] as $estado) {
            $original = ContabDocEncabezado::find($this->crearDocumento($estado)->id);
            $antes = $original->toArray();
            $lineasAntes = $original->lineas_registros->toArray();
            $consecutivoAntes = DB::table('core_consecutivos_documentos')->value('consecutivo_actual');

            $copia = (new DuplicarDocumentoContableControllerPrueba())->duplicar_documento($original->id);

            $this->assertNotEquals($original->id, $copia->id);
            $this->assertSame((int)$consecutivoAntes + 1, (int)$copia->consecutivo);
            $this->assertSame('Activo', $copia->estado);
            $this->assertSame('actual@appsiel.test', $copia->creado_por);
            $this->assertSame('actual@appsiel.test', $copia->modificado_por);
            $this->assertSame($original->descripcion, $copia->descripcion);
            $this->assertCount(2, $copia->lineas_registros);
            foreach ($copia->lineas_registros as $indice => $linea) {
                $this->assertSame('Activo', $linea->estado);
                $this->assertNotEquals($lineasAntes[$indice]['id'], $linea->id);
                $this->assertEquals($lineasAntes[$indice]['valor_debito'], $linea->valor_debito);
                $this->assertEquals($lineasAntes[$indice]['valor_credito'], $linea->valor_credito);
                $this->assertSame($lineasAntes[$indice]['tipo_transaccion'], $linea->tipo_transaccion);
            }
            $this->assertSame($antes, ContabDocEncabezado::find($original->id)->toArray());
            $this->assertSame($lineasAntes, ContabDocEncabezado::find($original->id)->lineas_registros->toArray());
        }
    }

    public function test_no_duplica_documentos_de_otra_empresa()
    {
        $original = $this->crearDocumento('Anulado');
        $original->update(['core_empresa_id' => 2]);

        try {
            (new DuplicarDocumentoContableControllerPrueba())->duplicar_documento($original->id);
            $this->fail('Debe rechazar el documento de otra empresa.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
            $this->assertSame(1, ContabDocEncabezado::count());
            $this->assertEquals(1895, DB::table('core_consecutivos_documentos')->value('consecutivo_actual'));
        }
    }

    protected function crearDocumento($estado)
    {
        $documento = ContabDocEncabezado::create([
            'core_empresa_id' => 1, 'core_tipo_doc_app_id' => 1, 'consecutivo' => 1895,
            'descripcion' => 'Nota original', 'estado' => $estado,
            'creado_por' => 'original@appsiel.test', 'modificado_por' => 'original@appsiel.test'
        ]);
        foreach (['crear_cxc', 'causacion'] as $indice => $tipo) {
            ContabDocRegistro::create([
                'contab_doc_encabezado_id' => $documento->id, 'tipo_transaccion' => $tipo,
                'valor_debito' => $indice === 0 ? 100 : 0,
                'valor_credito' => $indice === 1 ? 100 : 0, 'estado' => $estado
            ]);
        }
        return $documento;
    }
}

class DuplicarDocumentoContableControllerPrueba extends ContabilidadController
{
    public function edit($id)
    {
        if (!$this->duplicado) {
            throw new \RuntimeException('La copia debe abrirse en modo de edición de duplicado.');
        }
        return ContabDocEncabezado::findOrFail($id);
    }
}
