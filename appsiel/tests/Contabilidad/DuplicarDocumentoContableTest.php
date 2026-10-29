<?php

use App\Contabilidad\ContabDocEncabezado;
use App\Contabilidad\ContabDocRegistro;
use App\Contabilidad\ContabMovimiento;
use App\CxC\CxcMovimiento;
use App\CxP\CxpMovimiento;
use App\Http\Controllers\Contabilidad\ContabilidadController;
use App\Sistema\Modelo;
use App\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
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
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function () {
            return implode('', func_get_args());
        });

        foreach ([new ContabDocEncabezado, new ContabDocRegistro, new ContabMovimiento,
                  new CxcMovimiento, new CxpMovimiento, new Modelo] as $modelo) {
            Schema::create($modelo->getTable(), function (Blueprint $table) use ($modelo) {
                $table->increments('id');
                foreach ($modelo->getFillable() as $campo) {
                    $table->string($campo)->nullable();
                }
                $table->timestamps();
            });
        }
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
        Schema::create('contab_cuentas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo');
            $table->string('descripcion');
        });
        Schema::create('core_terceros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numero_identificacion');
            $table->string('descripcion');
        });
        DB::table('core_tipos_docs_apps')->insert(['id' => 1]);
        DB::table('contab_cuentas')->insert(['id' => 1, 'codigo' => '1305', 'descripcion' => 'Cartera']);
        DB::table('core_terceros')->insert(['id' => 1, 'numero_identificacion' => '123', 'descripcion' => 'Tercero original']);
        DB::table('core_consecutivos_documentos')->insert([
            'core_empresa_id' => 1, 'core_documento_app_id' => 1, 'consecutivo_actual' => 1895
        ]);
        DB::table('sys_modelos')->insert(['id' => 47, 'name_space' => ContabDocEncabezado::class]);
        $usuario = new User(['empresa_id' => 1, 'email' => 'actual@appsiel.test']);
        $usuario->id = 99;
        Auth::setUser($usuario);
        request()->replace(['id' => 14, 'id_modelo' => 47, 'id_transaccion' => 9]);
    }

    public function tearDown()
    {
        DB::purge('duplicar_contable_testing');
        Config::set('database.default', $this->conexionOriginal);
        parent::tearDown();
    }

    public function test_precarga_activos_y_anulados_con_cxc_y_cxp_sin_escribir_datos()
    {
        foreach (['Activo', 'Anulado'] as $estado) {
            $original = $this->crearDocumento($estado);
            $antes = ContabDocEncabezado::find($original->id)->toArray();
            $lineasAntes = $original->lineas_registros->toArray();
            DB::enableQueryLog();
            DB::flushQueryLog();

            $controlador = new DuplicarDocumentoContableControllerPrueba;
            $respuesta = $controlador->duplicar_documento($original->id);
            $this->assertSame(url('contabilidad/create?id=14&id_modelo=47&id_transaccion=9&duplicar_de='.$original->id), $respuesta->getTargetUrl());
            request()->merge(['duplicar_de' => $original->id]);
            $vista = $controlador->create();
            $datos = $vista->getData();
            $this->assertSame('contabilidad.create', $vista->name());
            $this->assertSame('contabilidad', $datos['form_create']['url']);
            $campos = array_column($datos['form_create']['campos'], null, 'name');
            $this->assertSame('2026-08-31', $campos['fecha']['value']);
            $this->assertArrayNotHasKey('disabled', $campos['fecha']['atributos']);
            $this->assertSame('Nota original', $campos['descripcion']['value']);
            $this->assertSame(['Tercero original (123)', '1'], $campos['core_tercero_id']['value']);
            $this->assertSame('Activo', $campos['estado']['value']);
            $this->assertSame('actual@appsiel.test', $campos['creado_por']['value']);
            $this->assertNull($campos['consecutivo']['value']);
            $this->assertSame(2, $datos['linea_num']);
            $this->assertContains('crear_cxc', $datos['lineas_documento']);
            $this->assertContains('crear_cxp', $datos['lineas_documento']);
            $this->assertContains('100', $datos['lineas_documento']);
            $this->assertContains('DETALLE ORIGINAL', $datos['lineas_documento']);
            foreach (DB::getQueryLog() as $consulta) {
                $this->assertSame(1, preg_match('/^select\b/i', $consulta['query']), $consulta['query']);
            }
            DB::disableQueryLog();
            $this->assertEquals(1895, DB::table('core_consecutivos_documentos')->value('consecutivo_actual'));
            $this->assertSame($antes, ContabDocEncabezado::find($original->id)->toArray());
            $this->assertSame($lineasAntes, ContabDocEncabezado::find($original->id)->lineas_registros->toArray());
            $this->assertSame(0, ContabMovimiento::count());
            $this->assertSame(0, CxcMovimiento::count());
            $this->assertSame(0, CxpMovimiento::count());
        }
    }

    public function test_conserva_vencimiento_y_soporte_de_las_lineas_al_precargar()
    {
        $original = $this->crearDocumento('Activo');
        foreach ($original->lineas_registros as $linea) {
            ContabMovimiento::create([
                'core_empresa_id' => 1, 'core_tipo_transaccion_id' => 9,
                'core_tipo_doc_app_id' => 1, 'consecutivo' => 1895,
                'id_registro_doc_tipo_transaccion' => $linea->id,
                'tipo_transaccion' => $linea->tipo_transaccion,
                'fecha_vencimiento' => '2026-11-30', 'documento_soporte' => 'SOP-LINEA'
            ]);
        }
        request()->merge(['duplicar_de' => $original->id]);
        $html = (new DuplicarDocumentoContableControllerPrueba)->create()->getData()['lineas_documento'];
        $dom = new DOMDocument;
        $dom->loadHTML('<table>'.$html.'</table>');
        foreach ($dom->getElementsByTagName('tr') as $fila) {
            $celdas = $fila->getElementsByTagName('td');
            $this->assertSame('2026-11-30', trim($celdas->item(0)->textContent));
            $this->assertSame('SOP-LINEA', trim($celdas->item(1)->textContent));
        }
        $this->assertSame(1, ContabDocEncabezado::count());
        $this->assertSame(2, ContabMovimiento::count());
    }

    public function test_al_guardar_crea_un_nuevo_documento_con_fecha_elegida_y_movimientos_cxc_cxp()
    {
        $original = $this->crearDocumento('Anulado');
        $lineas = [];
        foreach (['crear_cxc', 'crear_cxp'] as $indice => $tipo) {
            $lineas[] = [
                'Cuenta' => '1-1305 Cartera', 'Tercero' => '1-Tercero original',
                'Detalle' => 'Detalle ajustado', 'debito' => $indice === 0 ? '$150' : '$0',
                'credito' => $indice === 1 ? '$150' : '$0', 'tipo_transaccion' => $tipo,
                'fecha_vencimiento' => '2026-11-30', 'documento_soporte_tercero' => 'SOP-1'
            ];
        }
        $lineas[] = [];
        $lineas[] = [];
        $request = new Request([
            'url_id' => 14, 'url_id_modelo' => 47, 'url_id_transaccion' => 9,
            'core_empresa_id' => 1, 'core_tipo_doc_app_id' => 1, 'core_tipo_transaccion_id' => 9,
            'core_tercero_id' => 1, 'fecha' => '2026-10-29', 'descripcion' => 'Nota ajustada',
            'estado' => 'Activo', 'valor_total' => 150,
            'tabla_registros_documento' => json_encode($lineas)
        ]);
        (new ContabilidadController)->store($request);
        $nuevo = ContabDocEncabezado::orderBy('id', 'desc')->first();
        $this->assertSame(2, ContabDocEncabezado::count());
        $this->assertEquals(1896, $nuevo->consecutivo);
        $this->assertSame('2026-10-29', $nuevo->fecha);
        $this->assertSame('Activo', $nuevo->estado);
        $this->assertSame('Nota ajustada', $nuevo->descripcion);
        $this->assertCount(2, $nuevo->lineas_registros);
        $this->assertSame(2, ContabMovimiento::count());
        foreach ([CxcMovimiento::first(), CxpMovimiento::first()] as $movimiento) {
            $this->assertNotNull($movimiento);
            $this->assertEquals(1896, $movimiento->consecutivo);
            $this->assertSame('2026-10-29', $movimiento->fecha);
            $this->assertSame('2026-11-30', $movimiento->fecha_vencimiento);
            $this->assertEquals(150, $movimiento->saldo_pendiente);
        }
        $this->assertSame('Anulado', ContabDocEncabezado::find($original->id)->estado);
    }

    public function test_rechaza_documentos_ajenos_al_duplicar_y_precargar()
    {
        $original = $this->crearDocumento('Anulado');
        $original->update(['core_empresa_id' => 2]);
        foreach (['duplicar_documento', 'create'] as $metodo) {
            request()->merge(['duplicar_de' => $original->id]);
            try {
                (new DuplicarDocumentoContableControllerPrueba)->$metodo($original->id);
                $this->fail('Debe rechazar el documento de otra empresa.');
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $error) {
                $this->assertSame(1, ContabDocEncabezado::count());
                $this->assertEquals(1895, DB::table('core_consecutivos_documentos')->value('consecutivo_actual'));
            }
        }
    }

    public function test_creacion_normal_permanece_sin_precarga()
    {
        $datos = (new DuplicarDocumentoContableControllerPrueba)->create()->getData();
        $this->assertArrayNotHasKey('documento_origen', $datos);
        $this->assertArrayNotHasKey('lineas_documento', $datos);
    }

    protected function crearDocumento($estado)
    {
        $documento = ContabDocEncabezado::create([
            'core_empresa_id' => 1, 'core_tipo_doc_app_id' => 1, 'consecutivo' => 1895,
            'core_tipo_transaccion_id' => 9, 'core_tercero_id' => 1, 'fecha' => '2026-08-31',
            'descripcion' => 'Nota original', 'estado' => $estado,
            'creado_por' => 'original@appsiel.test', 'modificado_por' => 'original@appsiel.test'
        ]);
        foreach (['crear_cxc', 'crear_cxp'] as $indice => $tipo) {
            ContabDocRegistro::create([
                'contab_doc_encabezado_id' => $documento->id, 'tipo_transaccion' => $tipo,
                'contab_cuenta_id' => 1, 'core_tercero_id' => 1, 'detalle_operacion' => 'DETALLE ORIGINAL',
                'valor_debito' => $indice === 0 ? 100 : 0,
                'valor_credito' => $indice === 1 ? 100 : 0, 'estado' => $estado
            ]);
        }
        return $documento;
    }
}

// Omite únicamente la configuración genérica del formulario; usa la precarga,
// las consultas de líneas y el guardado reales del controlador.
class DuplicarDocumentoContableControllerPrueba extends ContabilidadController
{
    public function set_variables_globales()
    {
        $this->transaccion = (object)['id' => 9];
    }

    public function crear($app, $modelo, $transaccion, $vista, $tabla = null, $item_sugerencia_cliente = null)
    {
        $campos = [];
        foreach (['fecha', 'descripcion', 'core_tercero_id', 'estado', 'creado_por', 'consecutivo'] as $nombre) {
            $campos[] = [
                'name' => $nombre, 'tipo' => $nombre === 'core_tercero_id' ? 'input_lista_sugerencias' : 'text',
                'value' => $nombre === 'estado' ? 'Activo' : ($nombre === 'creado_por' ? Auth::user()->email : null),
                'atributos' => []
            ];
        }
        return view($vista, ['form_create' => ['url' => 'contabilidad', 'campos' => $campos]]);
    }
}
