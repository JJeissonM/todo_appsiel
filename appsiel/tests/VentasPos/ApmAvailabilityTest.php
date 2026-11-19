<?php

use App\Http\Middleware\EnsureApmEnabled;
use App\Ventas\ApmDevice;
use App\Ventas\Services\ApmAvailability;
use Illuminate\Http\Request;

class ApmAvailabilityTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config([
            'ventas.metodo_impresion_pedido_ventas' => 'normal',
            'ventas.metodo_impresion_pedido_restaurante' => 'normal',
            'ventas.metodo_impresion_factura_pos' => 'normal',
            'ventas_pos.apm_scale_listener_activo' => 0,
            'tesoreria.usar_apm_pago_cxp' => 0
        ]);
    }

    public function test_sin_apm_no_se_cargan_dispositivos_ni_cliente_en_el_pos()
    {
        $this->assertFalse(ApmAvailability::enabled());
        $this->assertSame([], ApmDevice::frontConfigByDeviceIds(['IMPRESORA LOCAL']));
        $this->assertSame(['' => ''], ApmDevice::scaleOptions());
        $this->assertNotContains('assets/js/apm/client.js', view('ventas.apm_scripts')->render());
        $this->assertNotContains('JSPrintManager.js', view('ventas_pos.formatos_impresion.scripts_comanda_jspm', [
            'params_JSPrintManager' => (object)['usar_complemento_JSPrintManager' => 0]
        ])->render());
    }

    /** @dataProvider activadores */
    public function test_cada_funcion_apm_activa_el_modulo($key, $value)
    {
        config([$key => $value]);
        $this->assertTrue(ApmAvailability::enabled());
        $this->assertContains('assets/js/apm/client.js', view('ventas.apm_scripts')->render());
    }

    public function activadores()
    {
        return [
            ['ventas.metodo_impresion_pedido_ventas', 'apm'],
            ['ventas.metodo_impresion_pedido_restaurante', 'apm'],
            ['ventas.metodo_impresion_factura_pos', 'apm'],
            ['ventas_pos.apm_scale_listener_activo', '1'],
            ['tesoreria.usar_apm_pago_cxp', '1']
        ];
    }

    /** @dataProvider rutasApm */
    public function test_rutas_apm_no_disponibles_sin_activar($uri)
    {
        $this->setExpectedException('Symfony\Component\HttpKernel\Exception\NotFoundHttpException');
        (new EnsureApmEnabled())->handle(Request::create($uri), function () {
            $this->fail('No debe acceder a APM desactivado.');
        });
    }

    public function rutasApm()
    {
        return [['/apm_print_queue'], ['/apm_print_queue/prepare'], ['/apm_devices/1/test_cut'],
            ['/apm_print_jobs/1/mark_printed'], ['/tesoreria_pagos_cxp_apm_payload/1']];
    }

    public function test_facturacion_pos_y_electronica_siguen_disponibles_sin_apm()
    {
        foreach (['/pos_factura', '/pos_factura_electronica', '/pos_factura/create'] as $uri) {
            $this->assertSame('permitido', (new EnsureApmEnabled())->handle(Request::create($uri), function () {
                return 'permitido';
            }));
        }
        config(['ventas.metodo_impresion_factura_pos' => 'apm']);
        $this->assertSame('permitido', (new EnsureApmEnabled())->handle(Request::create('/apm_print_queue'), function () {
            return 'permitido';
        }));
    }

    public function test_jspm_se_carga_solo_para_punto_de_venta_que_lo_utiliza()
    {
        $data = ['params_JSPrintManager' => (object)['usar_complemento_JSPrintManager' => 1]];
        $this->assertContains('JSPrintManager.js', view('ventas_pos.formatos_impresion.scripts_comanda_jspm', $data)->render());
        config(['ventas.metodo_impresion_pedido_restaurante' => 'apm']);
        $this->assertNotContains('JSPrintManager.js', view('ventas_pos.formatos_impresion.scripts_comanda_jspm', $data)->render());
    }

    public function test_identifica_catalogos_apm_con_namespaces_antiguos_y_actuales()
    {
        $this->assertTrue(ApmAvailability::isApmModel('App\Ventas\ApmDevice'));
        $this->assertTrue(ApmAvailability::isApmModel('App\\Ventas\\ApmPrintJob'));
        $this->assertFalse(ApmAvailability::isApmModel('App\VentasPos\FacturaPos'));
    }

    public function test_oculta_menus_y_bloquea_catalogos_genericos_sin_afectar_modelos_pos()
    {
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        \Illuminate\Support\Facades\DB::purge('sqlite');
        $db = \Illuminate\Support\Facades\DB::connection('sqlite');
        $db->getSchemaBuilder()->create('sys_modelos', function ($table) {
            $table->increments('id');
            $table->string('name_space');
        });
        $db->getSchemaBuilder()->create('permissions', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('core_app_id');
            $table->integer('enabled');
            $table->integer('parent');
            $table->integer('orden');
        });
        $db->table('sys_modelos')->insert([
            ['id' => 1, 'name_space' => 'App\\Ventas\\ApmDevice'],
            ['id' => 2, 'name_space' => 'App\\VentasPos\\FacturaPos']
        ]);
        $db->table('permissions')->insert([
            ['name' => 'vtas_apm_devices', 'core_app_id' => 13, 'enabled' => 1, 'parent' => 0, 'orden' => 1],
            ['name' => 'vtas_facturas', 'core_app_id' => 13, 'enabled' => 1, 'parent' => 0, 'orden' => 2]
        ]);
        $menu = new \App\Core\Menu();
        $this->assertSame('vtas_facturas', $menu->optionsMenu(13)[0]['name']);
        $this->assertCount(1, $menu->optionsMenu(13));
        $next = function () { return 'permitido'; };
        $this->assertSame('permitido', (new EnsureApmEnabled())->handle(Request::create('/web?id_modelo=2'), $next));
        try {
            (new EnsureApmEnabled())->handle(Request::create('/web?id_modelo=1'), $next);
            $this->fail('El catalogo APM debe estar desactivado.');
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            $this->assertSame(404, $e->getStatusCode());
        }
        config(['ventas.metodo_impresion_factura_pos' => 'apm']);
        $this->assertCount(2, $menu->optionsMenu(13));
        $this->assertSame('permitido', (new EnsureApmEnabled())->handle(Request::create('/web?id_modelo=1'), $next));
    }
}
