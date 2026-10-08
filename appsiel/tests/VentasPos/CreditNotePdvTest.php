<?php

use App\Ventas\Services\NotaCreditoServices;
use Illuminate\Support\Facades\DB;

class CreditNotePdvService extends NotaCreditoServices
{
    public function resolve($invoice, $movement) { return $this->pdv_factura_relacionada($invoice, $movement); }
}

class CreditNotePdvTest extends TestCase
{
    public function test_hereda_pdv_del_movimiento_o_pos_de_la_misma_empresa()
    {
        $previous = DB::getDefaultConnection();
        config(['database.connections.credit_pdv_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('credit_pdv_test');
        try {
            DB::statement('CREATE TABLE vtas_pos_doc_encabezados (core_empresa_id INTEGER, core_tipo_transaccion_id INTEGER, core_tipo_doc_app_id INTEGER, consecutivo INTEGER, pdv_id INTEGER)');
            DB::table('vtas_pos_doc_encabezados')->insert([
                ['core_empresa_id'=>2,'core_tipo_transaccion_id'=>52,'core_tipo_doc_app_id'=>11,'consecutivo'=>70,'pdv_id'=>99],
                ['core_empresa_id'=>1,'core_tipo_transaccion_id'=>52,'core_tipo_doc_app_id'=>11,'consecutivo'=>70,'pdv_id'=>5]
            ]);
            $invoice = (object)['core_empresa_id'=>1,'core_tipo_transaccion_id'=>52,'core_tipo_doc_app_id'=>11,'consecutivo'=>70,'pdv_id'=>null];
            $service = new CreditNotePdvService();
            $this->assertSame(3, $service->resolve($invoice, (object)['pdv_id'=>3]));
            $this->assertSame(5, $service->resolve($invoice, (object)['pdv_id'=>null]));
            $invoice->consecutivo = 71;
            $this->assertNull($service->resolve($invoice, null));
            $invoice->pdv_id = 7;
            $this->assertSame(7, $service->resolve($invoice, null));
        } finally {
            DB::purge('credit_pdv_test'); DB::setDefaultConnection($previous);
        }
    }
}
