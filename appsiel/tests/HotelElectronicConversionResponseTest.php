<?php

use App\Hotel\Services\HotelService;
use App\VentasPos\FacturaPos;
use Illuminate\Http\JsonResponse;

class HotelConversionResponseService extends HotelService
{
    public function conversionUrl($response)
    {
        $invoice = new FacturaPos();
        $invoice->setRelation('tipo_documento_app', (object)['prefijo' => 'POS']);
        $invoice->consecutivo = 123;
        return $this->electronicConversionUrl($response, $invoice);
    }
}

class HotelElectronicConversionResponseTest extends TestCase
{
    public function test_extrae_la_url_de_la_respuesta_json_exitosa()
    {
        $response = new JsonResponse(['status' => 'success', 'url_print' => '/vtas_imprimir/42']);
        $this->assertSame('/vtas_imprimir/42', (new HotelConversionResponseService())->conversionUrl($response));
    }

    public function test_no_presenta_un_envio_fallido_como_exitoso_aunque_haya_url()
    {
        $this->setExpectedException(Exception::class, 'quedo contabilizada, pero no fue enviada');
        $response = new JsonResponse([
            'status' => 'error', 'url_print' => '/vtas_imprimir/42',
            'message' => 'La factura quedo contabilizada, pero no fue enviada: timeout',
        ], 502);
        (new HotelConversionResponseService())->conversionUrl($response);
    }

    public function test_rechaza_una_respuesta_exitosa_sin_url()
    {
        $this->setExpectedException(Exception::class);
        (new HotelConversionResponseService())->conversionUrl(new JsonResponse(['status' => 'success']));
    }
}
