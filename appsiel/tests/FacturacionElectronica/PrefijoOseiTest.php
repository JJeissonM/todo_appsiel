<?php

use App\FacturacionElectronica\OSEI\DocSoporte;
use App\FacturacionElectronica\OSEI\FacturaGeneralOsei;

class FakeDocEncabezadoPrefijo
{
    public $resolucion;
    public $tipo_documento_app;

    public function __construct($prefijoTipoDocApp = 'FVE')
    {
        $this->tipo_documento_app = (object) ['prefijo' => $prefijoTipoDocApp];
    }

    public function resolucion_facturacion()
    {
        return $this->resolucion;
    }
}

class PrefijoOseiTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['facturacion_electronica.fe_ambiente' => 'PRODUCCION']);
    }

    public function test_factura_en_produccion_usa_el_prefijo_de_la_resolucion_vigente()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRODUCCION']);

        $doc = new FakeDocEncabezadoPrefijo();
        $doc->resolucion = (object) ['prefijo' => 'FEV', 'numero_resolucion' => 1];

        $factura = new FacturaGeneralOsei($doc, 'factura');

        $this->assertSame('FEV', $factura->prefijo_osei());
    }

    public function test_factura_en_pruebas_usa_el_prefijo_literal_fee()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRUEBAS']);

        $doc = new FakeDocEncabezadoPrefijo();
        $doc->resolucion = (object) ['prefijo' => 'FEV', 'numero_resolucion' => 1];

        $factura = new FacturaGeneralOsei($doc, 'factura');

        $this->assertSame('FEE', $factura->prefijo_osei());
    }

    public function test_factura_en_produccion_sin_resolucion_cae_al_prefijo_del_tipo_documento_app()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRODUCCION']);

        $doc = new FakeDocEncabezadoPrefijo('FVE');
        $doc->resolucion = null;

        $factura = new FacturaGeneralOsei($doc, 'factura');

        $this->assertSame('FVE', $factura->prefijo_osei());
    }

    public function test_nota_credito_usa_siempre_el_prefijo_del_tipo_documento_app()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRODUCCION']);

        $doc = new FakeDocEncabezadoPrefijo('NCE');
        $doc->resolucion = (object) ['prefijo' => 'FEV', 'numero_resolucion' => 1];

        $notaCredito = new FacturaGeneralOsei($doc, 'nota_credito');

        $this->assertSame('NCE', $notaCredito->prefijo_osei());
    }

    public function test_documento_soporte_en_produccion_usa_el_prefijo_de_la_resolucion_vigente()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRODUCCION']);

        $doc = new FakeDocEncabezadoPrefijo();
        $doc->resolucion = (object) ['prefijo' => 'SETP', 'numero_resolucion' => 1];

        $docSoporte = new DocSoporte($doc, 'support_doc');

        $this->assertSame('SETP', $docSoporte->prefijo_osei());
    }

    public function test_documento_soporte_en_pruebas_usa_el_prefijo_literal_sett()
    {
        config(['facturacion_electronica.fe_ambiente' => 'PRUEBAS']);

        $doc = new FakeDocEncabezadoPrefijo();

        $docSoporte = new DocSoporte($doc, 'support_doc');

        $this->assertSame('SETT', $docSoporte->prefijo_osei());
    }
}
