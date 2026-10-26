<?php

use App\FacturacionElectronica\OSEI\FacturaGeneralOsei;

class OseiPosGenerationFixture extends FacturaGeneralOsei
{
    public function __construct($document)
    {
        $this->doc_encabezado = $document;
    }

    public function get_encabezado_factura($token)
    {
        return '"customer":{"legal_name":"José Peña & Cía.","email":"ventas+pos@example.com"},"notes":"Venta José","number":"POS-001"';
    }

    public function get_lineas_registros()
    {
        return '[{"description":"Café especial ®","quantity":2,"price":1500}]';
    }

    public function get_aditional_info()
    {
        return '{}';
    }
}

class OseiInvoiceGenerationTest extends TestCase
{
    public function test_el_generador_usado_por_pos_produce_json_normalizado()
    {
        config(['facturacion_electronica.enviar_email_clientes' => 'true',
            'facturacion_electronica.email_copia_factura' => '']);
        $document = (object)[
            'cliente_facturacion' => (object)['tercero' => (object)['email' => 'ventas+pos@example.com']],
            'ventas_doc_relacionado_id' => 0,
            'valor_ajuste_al_peso' => 0,
            'valor_total_bolsas' => 0,
        ];
        $generator = new OseiPosGenerationFixture($document);
        $json = $generator->preparar_cadena_json_factura('token');
        $payload = json_decode($json, true);

        $this->assertSame(JSON_ERROR_NONE, json_last_error());
        $this->assertSame('Jose Pena Cia', $payload['invoice']['customer']['legal_name']);
        $this->assertSame('Cafe especial', $payload['invoice']['items'][0]['description']);
        $this->assertSame('Venta Jose', $payload['invoice']['notes']);
        $this->assertSame('POS-001', $payload['invoice']['number']);
        $this->assertSame(2, $payload['invoice']['items'][0]['quantity']);
        $this->assertSame('ventas+pos@example.com', $payload['actions']['email']);
        $this->assertTrue($payload['actions']['send_email']);
        $this->assertSame($json, $generator->preparar_cadena_json_factura('token'));
    }
}
