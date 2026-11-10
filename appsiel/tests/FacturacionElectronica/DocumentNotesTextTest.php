<?php

use App\FacturacionElectronica\Services\DocumentNotesText;
use Illuminate\Container\Container;
use Illuminate\Config\Repository;

class NotesDataicoFixture extends App\FacturacionElectronica\DATAICO\FacturaGeneral
{
    public function get_datos_cliente() { return '{}'; }
}

class DocumentNotesTextTest extends PHPUnit_Framework_TestCase
{
    public function testEditorParagraphsEntitiesAndBreaksBecomePlainText()
    {
        $html = '<p>ORDEN DE COMPRA:&nbsp;4582875749</p><p>MIGO:&nbsp;8872812376</p>'
            . '<p><strong>PROVEEDOR:</strong> 101308495</p><p>COMPRADOR: Erick Daza&nbsp;</p>';
        $this->assertSame("ORDEN DE COMPRA: 4582875749\nMIGO: 8872812376\nPROVEEDOR: 101308495\nCOMPRADOR: Erick Daza",
            DocumentNotesText::normalize($html));
        $this->assertSame("Uno\nDos & tres", DocumentNotesText::normalize('Uno<br>Dos &amp; tres'));
        $this->assertSame('', DocumentNotesText::normalize(null));
        $this->assertSame('Texto simple', DocumentNotesText::normalize('Texto simple'));
    }

    public function testDataicoHeaderContainsValidJsonWithReadableNotesAndQuotes()
    {
        $previous = Container::getInstance() ?: new Container();
        $container = new Container();
        $container->instance('config', new Repository());
        Container::setInstance($container);
        try {
            $doc = (object)[
                'forma_pago' => 'credito', 'descripcion' => '<p>Orden: "Especial"&nbsp;&amp; Café</p><p>MIGO: 123</p>',
                'fecha_vencimiento' => '2026-12-20', 'fecha' => '2026-11-10', 'consecutivo' => 10016,
            ];
            $provider = new NotesDataicoFixture($doc, 'factura');
            $payload = json_decode('{'.$provider->get_encabezado_factura().'}', true);
            $this->assertSame(JSON_ERROR_NONE, json_last_error());
            $this->assertSame(["Orden: \"Especial\" & Café\nMIGO: 123"], $payload['notes']);
            $doc->descripcion = '<p>&nbsp;</p>';
            $payload = json_decode('{'.$provider->get_encabezado_factura().'}', true);
            $this->assertSame(['-'], $payload['notes']);
        } finally {
            Container::setInstance($previous);
        }
    }
}
