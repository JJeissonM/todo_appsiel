<?php

use App\FacturacionElectronica\OSEI\DocumentosOsei;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class DocumentosOseiTest extends TestCase
{
    private $history = [];

    private function withResponses(array $responses)
    {
        $this->history = [];
        $mock = new MockHandler($responses);
        $stack = HandlerStack::create($mock);
        $stack->push(\GuzzleHttp\Middleware::history($this->history));

        $client = new Client(['handler' => $stack]);

        return new class($client) extends DocumentosOsei {
            private $client;

            public function __construct($client)
            {
                $this->client = $client;
            }

            protected function client()
            {
                return $this->client;
            }
        };
    }

    public function test_the_url_uses_the_api_host_and_ignores_the_bot_url()
    {
        config([
            'facturacion_electronica.WSDL' => 'https://osei.com.co/api/v1/invoices',
            'facturacion_electronica.url_bot_osei' => 'https://osei.com.co/ed5b78db8a6c7d07/',
        ]);

        $documentos = $this->withResponses([new Response(200, [], '%PDF-1.4')]);
        $documentos->pdf('APSI', '837');

        $this->assertSame('https://osei.com.co/api/v1/documents/APSI/837/pdf', (string) $this->history[0]['request']->getUri());
    }

    public function test_pdf_sends_the_bearer_token_and_the_right_url()
    {
        config(['facturacion_electronica.tokenEmpresa' => 'token-de-prueba']);

        $documentos = $this->withResponses([new Response(200, [], '%PDF-1.4 contenido')]);
        $result = $documentos->pdf('FE', '100');

        $this->assertTrue($result['ok']);
        $this->assertSame('%PDF-1.4 contenido', $result['content']);

        $request = $this->history[0]['request'];
        $this->assertSame('Bearer token-de-prueba', $request->getHeaderLine('Authorization'));
        $this->assertSame('/api/v1/documents/FE/100/pdf', $request->getUri()->getPath());
    }

    public function test_a_404_from_osei_becomes_ok_false_with_the_message()
    {
        $documentos = $this->withResponses([
            new Response(404, [], json_encode(['status' => 'error', 'message' => 'No se encontró un documento aceptado por la DIAN con ese prefijo y número.'])),
        ]);

        $result = $documentos->pdf('FE', '999');

        $this->assertFalse($result['ok']);
        $this->assertNull($result['content']);
        $this->assertSame('No se encontró un documento aceptado por la DIAN con ese prefijo y número.', $result['message']);
    }

    public function test_resend_mail_sends_the_recipients_and_decodes_the_response()
    {
        $documentos = $this->withResponses([
            new Response(200, [], json_encode(['status' => 'success', 'message' => 'Envío correcto a: cliente@correo.com'])),
        ]);

        $result = $documentos->reenviarCorreo('FE', '100', ['cliente@correo.com']);

        $this->assertSame('success', $result['status']);
        $this->assertSame(200, $result['http_status']);

        $request = $this->history[0]['request'];
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('/api/v1/documents/FE/100/resend-mail', $request->getUri()->getPath());
        $this->assertSame(['recipients' => ['cliente@correo.com']], json_decode((string) $request->getBody(), true));
    }
}
