<?php

use App\Ventas\Cliente;
use App\VentasPos\FacturaPos;
use App\VentasPos\Services\InvoiceCustomerService;
use App\FacturacionElectronica\Services\DocumentHeaderService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceCustomerServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function customer()
    {
        $cliente = Cliente::join('core_terceros', 'core_terceros.id', '=', 'vtas_clientes.core_tercero_id')->select('vtas_clientes.*')->first();
        if (is_null($cliente)) {
            $this->markTestSkipped('Se requiere un cliente con tercero.');
        }
        return $cliente;
    }

    public function test_el_tercero_recibido_se_reemplaza_por_el_del_cliente()
    {
        $cliente = $this->customer();
        $request = new Request(['cliente_id' => $cliente->id, 'core_tercero_id' => 0]);

        (new InvoiceCustomerService())->normalizeRequest($request);

        $this->assertSame((int)$cliente->id, $request->cliente_id);
        $this->assertSame((int)$cliente->core_tercero_id, $request->core_tercero_id);
    }

    public function test_rechaza_un_cliente_inexistente()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        (new InvoiceCustomerService())->normalizeRequest(new Request(['cliente_id' => 0]));
    }

    public function test_acepta_una_factura_con_el_tercero_del_cliente()
    {
        $cliente = $this->customer();
        $invoice = new FacturaPos(['cliente_id' => $cliente->id, 'core_tercero_id' => $cliente->core_tercero_id]);
        $resolved = (new InvoiceCustomerService())->validateInvoice($invoice);
        $this->assertSame($cliente->id, $resolved->id);
    }

    public function test_la_conversion_rechaza_la_inconsistencia_antes_de_modificar_documentos()
    {
        $invoice = FacturaPos::first();
        if (is_null($invoice)) {
            $this->markTestSkipped('Se requiere una factura POS.');
        }
        $cliente = $this->customer();
        $otherThirdParty = DB::table('core_terceros')->where('id', '<>', $cliente->core_tercero_id)->value('id');
        if (is_null($otherThirdParty)) {
            $this->markTestSkipped('Se requiere otro tercero para simular la inconsistencia.');
        }
        DB::table('vtas_pos_doc_encabezados')->where('id', $invoice->id)->update([
            'cliente_id' => $cliente->id, 'core_tercero_id' => $otherThirdParty,
        ]);
        $before = (array)DB::table('vtas_pos_doc_encabezados')->where('id', $invoice->id)->first();
        $salesCount = DB::table('vtas_doc_encabezados')->count();
        Auth::shouldReceive('user')->andReturn((object)['email' => 'test@example.com']);

        $result = (new DocumentHeaderService())->convert_to_electronic_invoice($invoice->id);

        $this->assertSame('mensaje_error', $result->status);
        $this->assertContains('no corresponde', $result->message);
        $this->assertSame($salesCount, DB::table('vtas_doc_encabezados')->count());
        $this->assertSame($before, (array)DB::table('vtas_pos_doc_encabezados')->where('id', $invoice->id)->first());
    }
}
