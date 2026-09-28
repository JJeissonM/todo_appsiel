<?php

use App\Ventas\Services\OrderInvoiceResolver;
use App\Ventas\VtasDocEncabezado;
use Illuminate\Support\Facades\DB;

class OrderInvoiceResolverTest extends TestCase
{
    private $previous;
    protected function setUp()
    {
        parent::setUp();
        $this->previous = DB::getDefaultConnection();
        config(['database.connections.order_resolver_test' => ['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('order_resolver_test');
        foreach (['vtas_doc_encabezados', 'vtas_pos_doc_encabezados'] as $table) {
            DB::statement('CREATE TABLE '.$table.' (id INTEGER, core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, created_at TEXT)');
        }
        $this->insert('vtas_doc_encabezados', 20021, 70, '2026-04-19 19:54:54');
        $this->insert('vtas_doc_encabezados', 38951, 189, '2026-09-20 20:32:56');
        $this->insert('vtas_pos_doc_encabezados', 20021, 189, '2026-09-20 20:32:54');
    }
    private function insert($table, $id, $number, $date)
    {
        DB::table($table)->insert(['id'=>$id,'core_empresa_id'=>1,'core_tipo_transaccion_id'=>52,'core_tipo_doc_app_id'=>11,'consecutivo'=>$number,'created_at'=>$date]);
    }
    private function order()
    {
        return new VtasDocEncabezado(['ventas_doc_relacionado_id'=>20021,'core_empresa_id'=>1,'core_tipo_transaccion_id'=>60,'estado'=>'Facturado']);
    }
    protected function tearDown()
    {
        DB::purge('order_resolver_test'); DB::setDefaultConnection($this->previous);
        parent::tearDown();
    }
    public function test_colision_historica_resuelve_electronica_por_identidad_pos()
    {
        $order=$this->order(); $order->created_at='2026-09-20 20:07:14';
        $this->assertSame(38951, (int)$order->documento_ventas_padre()->id);
    }
    public function test_no_adivina_si_ambas_tablas_ofrecen_facturas_validas_distintas()
    {
        $order=$this->order(); $order->created_at='2026-01-01 00:00:00';
        $this->assertNull((new OrderInvoiceResolver())->resolve($order));
    }
    public function test_rechaza_facturas_de_otra_empresa()
    {
        $order=$this->order(); $order->core_empresa_id=2;
        $this->assertNull((new OrderInvoiceResolver())->resolve($order));
    }
    public function test_conserva_relacion_directa_de_ventas_sin_pos()
    {
        DB::table('vtas_pos_doc_encabezados')->delete();
        $order=$this->order(); $order->created_at='2026-01-01 00:00:00';
        $this->assertSame(20021, (int)(new OrderInvoiceResolver())->resolve($order)->id);
    }
}
