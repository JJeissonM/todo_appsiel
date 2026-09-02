<?php

use App\Compras\ComprasDocEncabezado;
use App\Compras\Proveedor;
use App\Compras\Services\CompraConfirmationService;
use App\Core\Services\TurnoModeResolver;
use App\Core\Services\TurnoFormService;
use App\Core\TurnoConfiguracion;
use App\Sistema\Modelo;
use App\User;
use App\Tesoreria\RegistrosMediosPago;
use App\Tesoreria\TesoCaja;
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

    public function test_factura_de_compra_rechaza_bodega_vacia_antes_de_crear_documentos()
    {
        $usuario = User::whereNotNull('empresa_id')->first();
        $this->assertNotNull($usuario);
        $this->be($usuario);
        $cantidadInicial = ComprasDocEncabezado::count();

        $this->call('POST', '/compras', array('inv_bodega_id' => ''));

        $this->assertResponseStatus(302);
        $this->assertSessionHas('mensaje_error', 'Debe seleccionar una bodega activa de la empresa para registrar la factura de compra.');
        $this->assertSame($cantidadInicial, ComprasDocEncabezado::count());
    }

    public function test_frontend_de_compras_exige_bodega_antes_de_serializar_lineas()
    {
        $script = file_get_contents(dirname(base_path()) . '/assets/js/compras/functions_create.js');

        $this->assertContains('if ( !validar_bodega_compra() ) { return false; }', $script);
        $this->assertContains('function validar_bodega_compra()', $script);
        $this->assertContains('Debe seleccionar la bodega donde ingresará la mercancía.', $script);
    }

    public function test_validacion_frontend_de_pago_respeta_la_caja_predeterminada_de_compras()
    {
        $view = file_get_contents(resource_path('views/compras/create.blade.php'));

        $this->assertContains('function validar_medio_pago_factura_contado()', $view);
        $this->assertContains("parseInt($('#compras_teso_caja_id_default').val(), 10) > 0", $view);
        $this->assertContains("$('#ingreso_registros_medios_recaudo tbody tr').length", $view);
        $this->assertContains('Debe ingresar un medio de pago cuando la factura es de contado.', $view);
        $this->assertLessThan(
            strpos($view, "var cantidad_lineas = $('#ingreso_registros_medios_recaudo tbody tr').length"),
            strpos($view, "parseInt($('#compras_teso_caja_id_default').val(), 10) > 0")
        );
    }

    public function test_caja_predeterminada_genera_el_pago_automatico_de_la_compra()
    {
        $caja = TesoCaja::first();
        $this->assertNotNull($caja);
        config(array('compras.teso_caja_id_default' => (int)$caja->id));

        $lineas = app(RegistrosMediosPago::class)->get_lineas_recaudos(
            json_encode(array(array('valor' => '$0.00'))),
            array(),
            12500,
            'compras'
        );

        $this->assertCount(1, $lineas);
        $this->assertSame((int)$caja->id, (int)explode('-', $lineas[0]->teso_caja_id)[0]);
        $this->assertSame('$12500', $lineas[0]->valor);
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
