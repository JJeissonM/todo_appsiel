<?php

use App\Compras\ComprasDocEncabezado;
use App\Compras\Proveedor;
use App\Compras\Services\CompraConfirmationService;
use App\Core\Services\TurnoModeResolver;
use App\Core\Services\TurnoFormService;
use App\Core\TurnoConfiguracion;
use App\Sistema\Modelo;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasTurnoPropagationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_factura_de_compra_declara_y_persiste_el_turno_del_efecto_de_inventario()
    {
        $documento = new ComprasDocEncabezado();

        $this->assertContains('turno_operativo_id', $documento->getFillable());
        $this->assertSame(
            'inventarios',
            config('turnos.manual_assignment_models.' . ComprasDocEncabezado::class)
        );
        $this->assertSame('inventarios', $documento->getTurnoModuleName());
    }

    public function test_confirmacion_diferida_propaga_el_turno_persistido_a_inventarios()
    {
        $documento = new ComprasDocEncabezado(array(
            'core_empresa_id' => 1,
            'core_tipo_transaccion_id' => 25,
            'core_tipo_doc_app_id' => 1,
            'consecutivo' => 10,
            'fecha' => '2026-09-01',
            'core_tercero_id' => 1,
            'proveedor_id' => 1,
            'forma_pago' => 'credito',
            'descripcion' => 'Compra de prueba',
            'creado_por' => 'integracion@appsiel.test',
            'turno_operativo_id' => 321,
        ));

        $proveedor = new Proveedor();
        $proveedor->inv_bodega_id = 5;
        $proveedor->clase_proveedor_id = 1;
        $proveedor->liquida_impuestos = 1;

        $documento->setRelation('proveedor', $proveedor);
        $documento->setRelation('lineas_registros', new Collection());

        $request = (new TestableCompraConfirmationService())->requestFrom($documento);

        $this->assertSame(321, (int)$request->input('turno_operativo_id'));
        $this->assertSame(1, (int)$request->input('core_empresa_id'));
        $this->assertSame(5, (int)$request->input('inv_bodega_id'));
    }

    public function test_create_de_compras_expone_selector_ajax_cuando_inventarios_usa_turnos()
    {
        $modelo = Modelo::where('name_space', ComprasDocEncabezado::class)->first();
        $usuario = User::whereNotNull('empresa_id')->first();

        $this->assertNotNull($modelo);
        $this->assertNotNull($usuario);
        $this->be($usuario);

        DB::table('core_turno_configuraciones')
            ->where('core_empresa_id', (int)$usuario->empresa_id)
            ->delete();
        DB::table('core_turno_configuraciones')->insert(array(
            'core_empresa_id' => (int)$usuario->empresa_id,
            'modulo' => 'inventarios',
            'contexto_tipo' => '*',
            'contexto_id' => 0,
            'modo' => TurnoConfiguracion::MODO_TURNOS,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        app(TurnoModeResolver::class)->clearCache();

        $this->assertTrue(Schema::hasColumn('compras_doc_encabezados', 'turno_operativo_id'));
        $this->assertArrayHasKey(ComprasDocEncabezado::class, config('turnos.manual_assignment_models'));

        $registro = new ComprasDocEncabezado(array(
            'core_empresa_id' => (int)$usuario->empresa_id,
        ));
        $formService = app(TurnoFormService::class);
        $campos = $formService->decorate($modelo, $registro, 'create', array());
        $campoTurno = null;
        foreach ($campos as $campo) {
            if (isset($campo['name']) && $campo['name'] === 'turno_operativo_id') {
                $campoTurno = $campo;
                break;
            }
        }

        $this->assertNotNull($campoTurno, json_encode($campos));
        $this->assertSame('input_lista_sugerencias', $campoTurno['tipo']);
        $this->assertContains('turnos/operativos/sugerencias', $campoTurno['atributos']['data-url_busqueda']);
        $this->assertSame('inventarios', $campoTurno['atributos']['data-turno-module']);
    }
}

class TestableCompraConfirmationService extends CompraConfirmationService
{
    public function requestFrom(ComprasDocEncabezado $documento)
    {
        return $this->buildRequestFromDocument($documento);
    }

    protected function getMotiveDefaultId()
    {
        return 0;
    }
}
