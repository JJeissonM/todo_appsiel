<?php

use App\NominaElectronica\Services\RepresentacionGraficaService;

class RepresentacionGraficaServiceTest extends TestCase
{
    protected $pdf;

    public function setUp()
    {
        parent::setUp();
        $this->pdf = "%PDF-1.4\n%%EOF";
    }

    /** @test */
    public function extrae_el_pdf_del_sobre_de_evidencia_de_dataico()
    {
        $respuesta = json_encode([
            'http_status' => 200,
            'body_json' => [
                'dian_status' => 'DIAN_ACEPTADO',
                'pdf' => base64_encode($this->pdf),
            ],
        ]);

        $extraido = (new RepresentacionGraficaService())->extraerPdf($respuesta);

        $this->assertSame($this->pdf, $extraido);
    }

    /** @test */
    public function extrae_un_pdf_anidado_de_una_respuesta_osei()
    {
        $respuesta = json_encode([
            'success' => true,
            'data' => [
                'graphicRepresentation' => 'data:application/pdf;base64,' . base64_encode($this->pdf),
            ],
        ]);

        $extraido = (new RepresentacionGraficaService())->extraerPdf($respuesta);

        $this->assertSame($this->pdf, $extraido);
    }

    /** @test */
    public function rechaza_contenido_que_no_es_un_pdf_valido()
    {
        $respuesta = json_encode(['pdf' => base64_encode('contenido no PDF')]);

        $this->assertNull((new RepresentacionGraficaService())->extraerPdf($respuesta));
    }
}
