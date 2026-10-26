<?php

use App\FacturacionElectronica\OSEI\PayloadNormalizer;

class OseiPayloadNormalizerTest extends PHPUnit_Framework_TestCase
{
    public function test_normaliza_documentos_anidados_sin_cambiar_campos_tecnicos_ni_tipos()
    {
        foreach (['invoice', 'credit_note', 'support_doc'] as $document) {
            $original = [
                $document => [
                    'customer' => ['legal_name' => 'NELSON SIMÓN PEÑALOZA & Cía.'],
                    'items' => [['description' => "Pollo\n" . 'especial ® ½']],
                    'notes' => "Cafe\xcc\x81 y pingüino 😊",
                    'email' => 'ventas+fe@empresa.com',
                    'url' => 'https://example.com/a?x=1&y=2',
                    'date' => '26/10/2026',
                    'sku' => 'AB-01/2',
                    'quantity' => 1.5,
                    'enabled' => false,
                    'optional' => null,
                ],
            ];
            $normalized = PayloadNormalizer::normalize($original);
            $this->assertSame('NELSON SIMON PENALOZA Cia', $normalized[$document]['customer']['legal_name']);
            $this->assertSame('Pollo especial', $normalized[$document]['items'][0]['description']);
            $this->assertSame('Cafe y pinguino', $normalized[$document]['notes']);
            foreach (['email', 'url', 'date', 'sku', 'quantity', 'enabled', 'optional'] as $key) {
                $this->assertSame($original[$document][$key], $normalized[$document][$key]);
            }
            $this->assertSame($normalized, PayloadNormalizer::normalize($normalized));
            $this->assertSame($normalized, json_decode(json_encode($normalized), true));
            $this->assertSame('NELSON SIMÓN PEÑALOZA & Cía.', $original[$document]['customer']['legal_name']);
        }
    }
}
