<?php

use App\VentasPos\Services\ReportsServices;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Factory;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;

// No inicia Laravel ni usa su conexión configurada: todas las escrituras de
// estas pruebas se realizan exclusivamente en una base SQLite en memoria.
class ArqueoCajaPdvCompartidoTest extends PHPUnit_Framework_TestCase
{
    protected $db;
    protected $previousContainer;
    protected $previousFacade;
    protected $previousResolver;

    protected function setUp()
    {
        parent::setUp();
        $this->previousContainer = Container::getInstance();
        $this->previousFacade = Facade::getFacadeApplication();
        $this->previousResolver = Model::getConnectionResolver();
        $container = new Container();
        $container->instance('config', new Illuminate\Config\Repository([
            'ventas_pos' => ['motivo_tesoreria_propinas' => 99, 'motivo_tesoreria_datafono' => 98],
            'tesoreria' => ['excluir_movimientos_bancarios_otros_usuarios' => 0]
        ]));
        $this->db = new Capsule($container);
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->db->bootEloquent();
        $auth = Mockery::mock();
        $auth->shouldReceive('check')->andReturn(false);
        $container->instance(Factory::class, $auth);
        Container::setInstance($container);
        Facade::setFacadeApplication($container);

        $tables = [
            'vtas_pos_puntos_de_ventas' => ['id', 'core_empresa_id', 'caja_default_id'],
            'vtas_pos_doc_encabezados' => ['id', 'core_empresa_id', 'pdv_id', 'consecutivo', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'valor_total', 'valor_total_bolsas'],
            'teso_movimientos' => ['id', 'core_empresa_id', 'pdv_id', 'turno_operativo_id', 'consecutivo', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'teso_caja_id', 'teso_cuenta_bancaria_id', 'teso_motivo_id', 'valor_movimiento'],
            'teso_cuentas_bancarias' => ['id', 'core_empresa_id', 'entidad_financiera_id'],
            'teso_entidades_financieras' => ['id'], 'teso_motivos' => ['id'], 'users' => ['id', 'empresa_id']
        ];
        foreach ($tables as $name => $columns) {
            $this->db->getConnection()->getSchemaBuilder()->create($name, function (Blueprint $table) use ($columns) {
                foreach ($columns as $column) $table->integer($column)->nullable();
                foreach (['fecha', 'estado', 'creado_por', 'descripcion', 'forma_pago', 'email'] as $column) $table->string($column)->nullable();
            });
        }
        $this->db->getConnection()->table('vtas_pos_puntos_de_ventas')->insert([
            ['id' => 1, 'core_empresa_id' => 1, 'caja_default_id' => 1],
            ['id' => 6, 'core_empresa_id' => 1, 'caja_default_id' => 1],
            ['id' => 7, 'core_empresa_id' => 1, 'caja_default_id' => 2],
            ['id' => 8, 'core_empresa_id' => 2, 'caja_default_id' => 1]
        ]);
        $this->db->getConnection()->table('teso_entidades_financieras')->insert(['id' => 1, 'descripcion' => 'Banco prueba']);
        $this->db->getConnection()->table('teso_cuentas_bancarias')->insert(['id' => 3, 'core_empresa_id' => 1, 'entidad_financiera_id' => 1, 'descripcion' => 'Transferencias', 'estado' => 'Activo']);
        $this->db->getConnection()->table('teso_motivos')->insert(['id' => 1, 'descripcion' => 'Venta']);
        foreach ([6 => 16000, 7 => 777000] as $pdv => $amount) {
            $this->db->getConnection()->table('vtas_pos_doc_encabezados')->insert([
                'id' => $pdv, 'core_empresa_id' => 1, 'pdv_id' => $pdv, 'fecha' => '2026-11-21',
                'estado' => 'Pendiente', 'creado_por' => 'cajero@test', 'forma_pago' => 'contado',
                'valor_total' => $amount, 'valor_total_bolsas' => 600,
                'consecutivo' => $pdv, 'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 52
            ]);
            $this->movement($pdv, $pdv, $amount, 47, 1);
        }
        $this->movement(20, null, 5000, 8, 1);
        $this->movement(21, 6, 1000, 47, 99, 6);
    }

    protected function tearDown()
    {
        $this->db->getConnection()->disconnect();
        if ($this->previousResolver) {
            Model::setConnectionResolver($this->previousResolver);
        } else {
            Model::unsetConnectionResolver();
        }
        Container::setInstance($this->previousContainer ?: new Container());
        Facade::setFacadeApplication($this->previousFacade);
        Mockery::close();
        parent::tearDown();
    }

    public function test_resumen_usa_el_pdv_del_arqueo_aunque_otro_pdv_use_la_misma_caja()
    {
        $service = new ReportsServices();
        $legacy = $service->resumen_ventas_arqueo_caja('2026-11-21', 1, 'cajero@test');
        $this->assertSame(0, $legacy->cantidad_facturas);
        $result = $service->resumen_ventas_arqueo_caja('2026-11-21', 1, 'cajero@test', 6, 1);
        $this->assertSame(1, $result->cantidad_facturas);
        $this->assertEquals(16000, $result->totales_cuentas_bancarias[0]['total']);
    }

    public function test_movimientos_incluyen_el_pdv_correcto_y_legacy_sin_mezclar_otra_caja()
    {
        $result = (new ReportsServices())->get_movimentos_cuentas_bancarias('2026-11-21', 1, 'cajero@test', null, 1, 6)->flatten(1);
        $this->assertEquals([6, 20], $result->pluck('id')->all());
        $this->assertEquals(21000, $result->sum('valor_movimiento'));
    }

    public function test_propinas_y_bolsas_usan_el_mismo_pdv_del_arqueo()
    {
        $service = new ReportsServices();
        $tips = $service->resumen_propinas_arqueo_caja('2026-11-21', 1, 'cajero@test', 6, 1);
        $this->assertEquals(1000, $tips->totales_cuentas_bancarias[0]['total']);
        $bags = $service->resumen_ingresos_bolsas('2026-11-21', 1, 6, 1);
        $this->assertEquals(600, $bags->valor_total_bolsas);
    }

    public function test_pdv_de_otra_empresa_o_caja_no_se_sustituye_por_el_primero()
    {
        foreach ([7, 8, 999] as $pdv) {
            $service = new ReportsServices();
            $this->assertSame('error', $service->resumen_ventas_arqueo_caja('2026-11-21', 1, null, $pdv, 1)->status);
            $this->assertTrue($service->get_movimentos_cuentas_bancarias('2026-11-21', 1, null, null, 1, $pdv)->isEmpty());
        }
    }

    public function test_turno_explicito_conserva_prioridad_sobre_el_pdv()
    {
        $this->db->getConnection()->table('teso_movimientos')->where('id', 6)->update(['turno_operativo_id' => 10]);
        $result = (new ReportsServices())->get_movimentos_cuentas_bancarias('2026-11-21', 1, null, 10, 1, 1)->flatten(1);
        $this->assertEquals([6], $result->pluck('id')->all());
    }

    protected function movement($id, $pdv, $amount, $transaction, $motive, $number = null)
    {
        $this->db->getConnection()->table('teso_movimientos')->insert([
            'id' => $id, 'core_empresa_id' => 1, 'pdv_id' => $pdv, 'fecha' => '2026-11-21',
            'consecutivo' => $number ?: $id, 'core_tipo_transaccion_id' => $transaction,
            'core_tipo_doc_app_id' => 52, 'teso_caja_id' => 0, 'teso_cuenta_bancaria_id' => 3,
            'teso_motivo_id' => $motive, 'valor_movimiento' => $amount, 'creado_por' => 'cajero@test'
        ]);
    }
}
