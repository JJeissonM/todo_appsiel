<?php

use App\Http\Controllers\VentasPos\FacturaElectronicaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AtomicStoreFacturaElectronicaController extends FacturaElectronicaController
{
    public function __construct() {}

    protected function find_existing_pos_invoice_by_uniqid($uniqid, Request $request = null)
    {
        return null;
    }
}

class ElectronicInvoiceAtomicStoreTest extends TestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_conversion_rechazada_revierte_el_pos_sin_confirmar_ni_enviar()
    {
        $invoicing = Mockery::mock('overload:App\VentasPos\Services\InvoicingService');
        $invoicing->shouldReceive('almacenar_factura_pos')->once()->andReturn((object)['id' => 123]);
        $conversion = Mockery::mock('overload:App\FacturacionElectronica\Services\DocumentHeaderService');
        $conversion->shouldReceive('convert_to_electronic_invoice')->with(123)->once()->andReturn((object)[
            'status' => 'mensaje_error', 'message' => 'Totales inconsistentes'
        ]);
        DB::shouldReceive('beginTransaction')->once()->ordered();
        DB::shouldReceive('rollBack')->once()->ordered();
        DB::shouldReceive('commit')->never();

        $request = new Request([
            'uniqid' => 'venta-original', 'creado_por' => 'test@example.com',
            'pedido_id' => 0, 'object_anticipos' => 'null'
        ]);
        $response = (new AtomicStoreFacturaElectronicaController())->store($request);
        $this->assertSame(422, $response->getStatusCode());
        $this->assertSame('Totales inconsistentes', $response->getData()->message);
        Mockery::close();
    }
}
