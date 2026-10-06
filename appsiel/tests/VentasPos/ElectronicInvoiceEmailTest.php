<?php

use App\FacturacionElectronica\Services\EmailNormalizer;
use App\FacturacionElectronica\Services\DocumentHeaderService;

class ElectronicInvoiceEmailTest extends PHPUnit_Framework_TestCase
{
    public function test_espacios_se_normalizan_antes_de_validar_sin_modificar_tercero()
    {
        foreach ([" cliente@example.com ", "cliente @example.com", "\tcliente@example.com\r\n", "\xC2\xA0cliente@example.com\xE2\x80\x8B"] as $email) {
            $tercero = (object)['email' => $email];
            $this->assertSame('cliente@example.com', EmailNormalizer::normalize($email));
            $this->assertSame('success', (new DocumentHeaderService())->validar_datos_tercero($tercero)->status);
            $this->assertSame($email, $tercero->email);
        }
    }

    public function test_correos_invalidos_siguen_rechazados()
    {
        foreach ([' ', 'sin-arroba', 'a@@example.com', 'a@example.com b@example.com'] as $email) {
            $this->assertSame('error', (new DocumentHeaderService())->validar_datos_tercero((object)['email'=>$email])->status);
        }
    }
}
