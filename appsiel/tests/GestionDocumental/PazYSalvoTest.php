<?php

class PazYSalvoTest extends TestCase
{
    private function renderReport()
    {
        return view('gestion_documental.pdf.paz_y_salvo', [
            'colegio' => (object)['descripcion' => 'Colegio de prueba', 'resolucion' => 'Resolución 123', 'ciudad' => 'Barrancas', 'direccion' => 'Calle 1', 'telefonos' => '1234567'],
            'estudiante' => (object)['nombre_completo' => 'Ana Pérez', 'numero_identificacion' => '001234567'],
            'tipo_documento' => 'Tarjeta de identidad',
            'array_fecha' => [22, 'noviembre', 2025],
            'firma' => (object)['titulo_tercero' => 'Tesorera'],
            'firmante' => (object)['tercero_nombre' => 'Doris Fuentes'],
            'texto_cumplimiento' => 'cumplió las obligaciones <especiales>.',
        ])->render();
    }

    public function test_renderiza_datos_fecha_firma_y_texto_sin_interpretar_html()
    {
        config(['matriculas.banner_reportes' => 'renderizado', 'matriculas.footer_reportes' => 'renderizado']);
        $html = html_entity_decode($this->renderReport(), ENT_QUOTES, 'UTF-8');
        foreach (['COLEGIO DE PRUEBA', 'ANA PÉREZ', '001234567', 'Tarjeta de identidad', '22 días del mes de', 'noviembre de 2025', 'DORIS FUENTES', 'Tesorera', 'Calle 1'] as $expected) {
            $this->assertContains($expected, $html);
        }
        $this->assertContains('&lt;especiales&gt;', $this->renderReport());
    }

    public function test_respeta_las_imagenes_configuradas_para_encabezado_y_pie()
    {
        config(['matriculas.banner_reportes' => 'imagen', 'matriculas.footer_reportes' => 'imagen',
            'matriculas.url_imagen_banner' => '/banner-prueba.png', 'matriculas.url_imagen_footer' => '/footer-prueba.png']);
        $html = $this->renderReport();
        $this->assertContains('src="/banner-prueba.png"', $html);
        $this->assertContains('src="/footer-prueba.png"', $html);
        $this->assertNotContains('Resolución 123', $html);
    }
}
