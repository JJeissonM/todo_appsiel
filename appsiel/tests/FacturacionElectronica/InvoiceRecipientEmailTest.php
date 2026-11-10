<?php

use App\FacturacionElectronica\Services\InvoiceRecipientEmail;
use App\FacturacionElectronica\Factura;
use App\Ventas\ContactoCliente;
use App\Ventas\Cliente;
use App\Core\Tercero;


class RecipientDataicoFixture extends \App\FacturacionElectronica\DATAICO\FacturaGeneral
{
    public function get_encabezado_factura() { return '"customer": ' . $this->get_datos_cliente(); }
    public function get_lineas_registros() { return '[]'; }
}

class RecipientOseiFixture extends \App\FacturacionElectronica\OSEI\FacturaGeneralOsei
{
    public function get_encabezado_factura($auth_token) { return '"customer": ' . $this->get_datos_cliente(); }
    public function get_lineas_registros() { return '[]'; }
    public function get_aditional_info() { return '{}'; }
}

class InvoiceRecipientEmailTest extends PHPUnit_Framework_TestCase
{
    private function document($contactEmail = null, $contactClient = 15)
    {
        $document = new Factura();
        $document->cliente_id = 15;
        $document->contacto_cliente_id = $contactEmail === null ? 0 : 20;
        $customer = new Cliente();
        $third = new Tercero();
        $third->email = 'principal@example.com';
        $customer->setRelation('tercero', $third);
        $document->setRelation('cliente_facturacion', $customer);
        if ($contactEmail !== null) {
            $contact = new ContactoCliente();
            $contact->cliente_id = $contactClient;
            $third = new Tercero();
            $third->email = $contactEmail;
            $contact->setRelation('tercero', $third);
            $document->setRelation('contacto_cliente', $contact);
        }
        return $document;
    }

    public function testSelectedContactIsPersistableAndItsNormalizedEmailTakesPriority()
    {
        $document = $this->document(" contacto@example.com\n");
        $this->assertTrue($document->isFillable('contacto_cliente_id'));
        $this->assertSame('contacto@example.com', InvoiceRecipientEmail::resolve($document));
        $this->assertSame('principal@example.com', $document->cliente_facturacion->tercero->email);
    }

    public function testNoContactEmptyEmailAndContactFromAnotherCustomerUseOriginalEmail()
    {
        foreach ([$this->document(), $this->document('  '), $this->document('otro@example.com', 99)] as $document) {
            $this->assertSame('principal@example.com', InvoiceRecipientEmail::resolve($document));
        }
    }
    public function testBothJsonProvidersUseContactEmailInCustomerAndDeliveryWithoutSending()
    {
        $oldContainer = \Illuminate\Container\Container::getInstance() ?: new \Illuminate\Container\Container();
        $container = new \Illuminate\Container\Container();
        $container->instance('config', new \Illuminate\Config\Repository([
            'facturacion_electronica' => [
                'enviar_email_clientes' => 'true',
                'email_copia_factura' => 'copia@example.com',
            ],
        ]));
        \Illuminate\Container\Container::setInstance($container);
        try {
            $document = $this->document('destino@example.com');
            $document->cliente_facturacion->tercero->setRelation('ciudad', (object)[
                'id' => '16920001', 'descripcion' => 'Valledupar',
            ]);
            $document->cliente_facturacion->tercero->descripcion = 'Cliente prueba';
            $document->cliente_facturacion->tercero->numero_identificacion = '900111222';
            $document->cliente_facturacion->tercero->telefono1 = '3001112222';
            foreach ([new RecipientDataicoFixture($document, 'factura'), new RecipientOseiFixture($document, 'factura')] as $provider) {
                $payload = json_decode($provider->preparar_cadena_json_factura('token-prueba'), true);
                $this->assertSame(JSON_ERROR_NONE, json_last_error());
                $this->assertSame('destino@example.com;copia@example.com', $payload['actions']['email']);
                $this->assertSame('destino@example.com', $payload['invoice']['customer']['email']);
            }
        } finally {
            \Illuminate\Container\Container::setInstance($oldContainer);
        }
    }

}
