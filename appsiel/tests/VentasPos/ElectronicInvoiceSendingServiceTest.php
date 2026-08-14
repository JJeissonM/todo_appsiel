<?php

use App\FacturacionElectronica\Factura;
use App\VentasPos\Services\ElectronicInvoiceSendingService;

class FakeElectronicInvoiceForSending extends Factura
{
    public $responses = [];
    public $sendCalls = 0;

    public function enviar_al_proveedor_tecnologico()
    {
        $response = $this->responses[$this->sendCalls];
        $this->sendCalls++;

        return $response;
    }
}

class TestableElectronicInvoiceSendingService extends ElectronicInvoiceSendingService
{
    public $waits = [];

    protected function validate(Factura $invoice)
    {
        // Los totales se prueban en InvoiceTotalsService; aqui se aisla el retry.
    }

    protected function waitBeforeRetry($milliseconds)
    {
        $this->waits[] = $milliseconds;
    }
}

class ElectronicInvoiceSendingServiceTest extends TestCase
{
    public function test_reintenta_un_error_transitorio_y_se_detiene_al_enviar()
    {
        $invoice = new FakeElectronicInvoiceForSending();
        $invoice->responses = [
            (object)['tipo' => 'mensaje_error', 'contenido' => 'Error de red/peticion: timeout'],
            (object)['tipo' => 'flash_message', 'contenido' => 'Enviada'],
        ];
        $service = new TestableElectronicInvoiceSendingService();

        $result = $service->send($invoice, 3, 750);

        $this->assertSame('flash_message', $result->tipo);
        $this->assertSame(2, $result->intentos);
        $this->assertSame(2, $invoice->sendCalls);
        $this->assertSame([750], $service->waits);
    }

    public function test_no_reintenta_errores_de_validacion_de_la_dian()
    {
        $invoice = new FakeElectronicInvoiceForSending();
        $invoice->responses = [
            (object)['tipo' => 'mensaje_error', 'contenido' => 'Documento rechazado. Presenta errores de validacion.'],
        ];
        $service = new TestableElectronicInvoiceSendingService();

        $result = $service->send($invoice, 3, 750);

        $this->assertSame('mensaje_error', $result->tipo);
        $this->assertFalse($result->reintentable);
        $this->assertSame(1, $invoice->sendCalls);
        $this->assertSame([], $service->waits);
    }

    public function test_reintenta_una_respuesta_vacia_del_proveedor()
    {
        $invoice = new FakeElectronicInvoiceForSending();
        $invoice->responses = [
            null,
            (object)['tipo' => 'flash_message', 'contenido' => 'Enviada'],
        ];
        $service = new TestableElectronicInvoiceSendingService();

        $result = $service->send($invoice, 2, 100);

        $this->assertSame('flash_message', $result->tipo);
        $this->assertSame(2, $invoice->sendCalls);
    }

    public function test_limita_a_cinco_el_numero_de_intentos()
    {
        $invoice = new FakeElectronicInvoiceForSending();
        $invoice->responses = array_fill(0, 5, (object)[
            'tipo' => 'mensaje_error',
            'contenido' => 'Error de servidor OSEI: HTTP 503',
        ]);
        $service = new TestableElectronicInvoiceSendingService();

        $result = $service->send($invoice, 20, 0);

        $this->assertSame('mensaje_error', $result->tipo);
        $this->assertSame(5, $result->intentos);
        $this->assertSame(5, $invoice->sendCalls);
    }
}
