<?php

use App\FacturacionElectronica\OSEI\FacturaGeneralOsei;

class OseiErrorResponseTest extends PHPUnit_Framework_TestCase
{
    private function decode($body)
    {
        $class = new ReflectionClass(FacturaGeneralOsei::class);
        $method = $class->getMethod('decodeErrorResponseBody');
        $method->setAccessible(true);

        return $method->invoke($class->newInstanceWithoutConstructor(), $body);
    }

    public function test_reemplaza_la_pagina_de_error_externa_por_un_aviso()
    {
        $result = $this->decode('<!DOCTYPE html><html><body><h1>Cannot use object as array</h1><div>Stack trace /home/servidor</div></body></html>');

        $this->assertContains('El servidor OSEI no pudo procesar la solicitud.', $result);
        $this->assertNotContains('<', $result);
        $this->assertNotContains('Stack trace', $result);
        $this->assertNotContains('/home/servidor', $result);
    }

    public function test_conserva_el_mensaje_de_validacion_json_y_escapa_html()
    {
        $this->assertSame('Certificado vencido', $this->decode('{"message":"Certificado vencido"}'));
        $this->assertSame('Valor &lt;invalido&gt; &amp; error', $this->decode('{"error":"Valor <invalido> & error"}'));
    }

    public function test_no_incrusta_html_dentro_de_un_mensaje_json()
    {
        $result = $this->decode(json_encode(['message' => '<iframe src="https://externo.example"></iframe>']));
        $this->assertContains('El servidor OSEI', $result);
        $this->assertNotContains('<iframe', $result);
    }

    public function test_conserva_errores_estructurados_y_respuestas_vacias()
    {
        $this->assertContains('El NIT es obligatorio', $this->decode('{"errors":{"nit":["El NIT es obligatorio"]}}'));
        $this->assertSame('', $this->decode(''));
        $this->assertSame('Servicio no disponible', $this->decode('Servicio no disponible'));
    }
}
