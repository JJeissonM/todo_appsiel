<?php

use App\VentasPos\Pdv;
use App\VentasPos\Services\ElectronicDocumentTypeService;

class ElectronicDocumentTypeServiceTest extends TestCase
{
    protected $service;

    protected function setUp()
    {
        parent::setUp();
        $this->service = new ElectronicDocumentTypeService();
        config(['facturacion_electronica.document_type_id_default' => 43]);
    }

    public function test_usa_el_tipo_de_documento_configurado_en_el_pdv()
    {
        $pdv = new Pdv(['document_type_id_default' => 91]);

        $this->assertSame(91, $this->service->resolveId($pdv));
    }

    public function test_usa_la_configuracion_global_cuando_el_campo_del_pdv_es_null()
    {
        $pdv = new Pdv(['document_type_id_default' => null]);

        $this->assertSame(43, $this->service->resolveId($pdv));
    }

    public function test_usa_la_configuracion_global_cuando_no_hay_pdv()
    {
        $this->assertSame(43, $this->service->resolveId());
    }

    public function test_el_modelo_convierte_la_seleccion_vacia_en_null()
    {
        $pdv = new Pdv(['document_type_id_default' => '']);

        $this->assertNull($pdv->document_type_id_default);
        $this->assertSame(43, $this->service->resolveId($pdv));
    }
}
