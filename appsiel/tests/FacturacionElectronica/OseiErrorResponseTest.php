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

    private function dianUnavailable($body, $tipoTransaccion = 'factura')
    {
        $class = new ReflectionClass(FacturaGeneralOsei::class);
        $instance = $class->newInstanceWithoutConstructor();

        $property = $class->getProperty('tipo_transaccion');
        $property->setAccessible(true);
        $property->setValue($instance, $tipoTransaccion);

        $method = $class->getMethod('dianUnavailableMessage');
        $method->setAccessible(true);

        return $method->invoke($instance, $body);
    }

    public function test_el_504_de_nginx_se_muestra_como_intermitencia_de_la_dian()
    {
        $nginx = '<html><head><title>504 Gateway Time-out</title></head><body><center><h1>504 Gateway Time-out</h1></center><hr><center>nginx</center></body></html>';

        $this->assertSame(
            'No podemos generar la factura por intermitencia en los servidores de la DIAN. Intente nuevamente en unos minutos.',
            $this->dianUnavailable($nginx)
        );
        $this->assertContains('No podemos generar la nota crédito', $this->dianUnavailable($nginx, 'nota_credito'));
    }

    public function test_usa_el_mensaje_de_osei_cuando_la_dian_no_responde()
    {
        $osei = json_encode([
            'status' => 'error',
            'error_code' => 'DIAN_NO_DISPONIBLE',
            'message' => 'No podemos generar la nota crédito por intermitencia en los servidores de la DIAN. Intente nuevamente en unos minutos.',
        ]);

        $this->assertContains('No podemos generar la nota crédito', $this->dianUnavailable($osei));
        // Versiones anteriores de OSEI respondían 504 con otro texto.
        $this->assertContains('No podemos generar la factura', $this->dianUnavailable('{"message":"Servidores DIAN colapsados, intente nuevamente más tarde"}'));
    }

    public function test_el_pos_sigue_reintentando_la_intermitencia_de_la_dian()
    {
        $this->assertTrue((new App\VentasPos\Services\ElectronicInvoiceSendingService())->isRetryableError($this->dianUnavailable('')));
    }

    public function test_conserva_errores_estructurados_y_respuestas_vacias()
    {
        $this->assertContains('El NIT es obligatorio', $this->decode('{"errors":{"nit":["El NIT es obligatorio"]}}'));
        $this->assertSame('', $this->decode(''));
        $this->assertSame('Servicio no disponible', $this->decode('Servicio no disponible'));
    }
}
