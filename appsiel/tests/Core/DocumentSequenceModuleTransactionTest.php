<?php

use App\Core\EncabezadoDocumentoTransaccion;
use App\Core\TipoDocApp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Regression of the shared factories with real module models, in traditional mode. */
class DocumentSequenceModuleTransactionTest extends TestCase
{
    private $modules = [
        'App\\VentasPos\\FacturaPos',
        'App\\Inventarios\\InvDocEncabezado',
        'App\\Tesoreria\\TesoDocEncabezado',
        'App\\Contabilidad\\ContabDocEncabezado',
    ];

    protected function setUp()
    {
        parent::setUp();
        config(['database.connections.sequence_modules_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::setDefaultConnection('sequence_modules_test');
        Schema::create('core_tipos_docs_apps', function ($table) { $table->increments('id'); });
        Schema::create('core_consecutivos_documentos', function ($table) {
            $table->increments('id');
            $table->integer('core_empresa_id');
            $table->integer('core_documento_app_id');
            $table->integer('consecutivo_actual');
            $table->timestamps();
            $table->unique(['core_empresa_id', 'core_documento_app_id']);
        });
        Schema::create('sys_modelos', function ($table) {
            $table->increments('id'); $table->string('name_space');
        });
        foreach ($this->modules as $index => $class) {
            $id = $index + 1;
            DB::table('core_tipos_docs_apps')->insert(['id' => $id]);
            DB::table('sys_modelos')->insert(['id' => $id, 'name_space' => $class]);
            $model = new $class;
            Schema::create($model->getTable(), function ($table) use ($model) {
                $table->increments('id');
                foreach ($model->getFillable() as $field) {
                    // This fixture exercises traditional operation without shifts.
                    if ($field !== 'turno_operativo_id') $table->string($field)->nullable();
                }
                $table->timestamps();
                $table->unique(['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo']);
            });
        }
    }

    protected function tearDown()
    {
        DB::purge('sequence_modules_test');
        parent::tearDown();
    }

    /** @dataProvider failureStages */
    public function test_module_headers_and_counters_commit_or_rollback_together($failureStage)
    {
        try {
            DB::transaction(function () use ($failureStage) {
                foreach ($this->modules as $index => $class) {
                    $id = $index + 1;
                    $header = (new EncabezadoDocumentoTransaccion($id))->crear_nuevo([
                        'core_empresa_id' => 1, 'core_tipo_doc_app_id' => $id,
                        'core_tipo_transaccion_id' => 40 + $id, 'fecha' => '2026-10-09',
                        'estado' => 'Contabilizado', 'descripcion' => 'Prueba aislada',
                    ]);
                    $this->assertInstanceOf($class, $header);
                    $this->assertEquals(1, $header->consecutivo);
                    $this->assertSame(1, DB::connection()->transactionLevel());
                    if ($failureStage === $id) throw new RuntimeException('Falla posterior al encabezado');
                }
            });
            $this->assertSame(0, $failureStage);
        } catch (RuntimeException $e) {
            $this->assertSame('Falla posterior al encabezado', $e->getMessage());
            $this->assertGreaterThan(0, $failureStage);
        }
        $this->assertSame(0, DB::connection()->transactionLevel());
        foreach ($this->modules as $index => $class) {
            $model = new $class;
            $this->assertSame($failureStage ? 0 : 1, $model->count());
            $this->assertSame($failureStage ? 0 : 1, TipoDocApp::get_consecutivo_actual(1, $index + 1));
        }
        // A failed operation leaves its number available for the next operation.
        if ($failureStage) {
            $header = (new EncabezadoDocumentoTransaccion(1))->crear_nuevo([
                'core_empresa_id' => 1, 'core_tipo_doc_app_id' => 1, 'core_tipo_transaccion_id' => 41,
            ]);
            $this->assertEquals(1, $header->consecutivo);
        }
    }

    public function failureStages() { return [[0], [1], [2], [3], [4]]; }
}
