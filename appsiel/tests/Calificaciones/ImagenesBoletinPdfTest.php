<?php

use App\Calificaciones\Services\ImagenesBoletinPdf;

class ImagenesBoletinPdfTest extends PHPUnit_Framework_TestCase
{
    private $directorio;
    private $png;

    public function setUp()
    {
        $this->directorio = sys_get_temp_dir() . '/boletin-imagenes-' . uniqid();
        mkdir($this->directorio);
        $imagen = imagecreatetruecolor(1, 1);
        imagepng($imagen, $this->directorio . '/firma uno.png');
        imagedestroy($imagen);
        $this->png = base64_encode(file_get_contents($this->directorio . '/firma uno.png'));
        file_put_contents($this->directorio . '/texto.png', 'no es una imagen');
    }

    public function tearDown()
    {
        foreach (glob($this->directorio . '/*') as $archivo) {
            unlink($archivo);
        }
        rmdir($this->directorio);
    }

    public function testIncrustaImagenesLocalesEnHtmlYCss()
    {
        $service = new ImagenesBoletinPdf(['https://colegio.test/storage/app/' => $this->directorio]);
        $url = 'https://colegio.test/storage/app/firma%20uno.png?v=1&amp;x=2';
        $html = '<img src="' . $url . '"><style>li {list-style-image: url(' . $url . ')}</style>';
        $resultado = $service->prepararHtml($html);

        $this->assertSame(2, substr_count($resultado, 'data:image/png;base64,' . $this->png));
        $this->assertNotContains('https://', $resultado);
    }

    public function testNormalizaDobleBarraGeneradaPorAssetEnEscudosYFirmas()
    {
        $service = new ImagenesBoletinPdf(['https://colegio.test/appsiel/storage/app/' => $this->directorio]);
        $html = '<img src="https://colegio.test/appsiel//storage/app/firma%20uno.png">';
        $this->assertSame('<img src="data:image/png;base64,' . $this->png . '">', $service->prepararHtml($html));
        $externa = '<img src="https://otro.test/appsiel//storage/app/firma%20uno.png">';
        $this->assertSame($externa, $service->prepararHtml($externa));
    }

    public function testDompdfRenderizaLaImagenIncrustadaSinAccesoRemoto()
    {
        $service = new ImagenesBoletinPdf(['https://colegio.test/' => $this->directorio]);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isRemoteEnabled', false);
        $dompdf->set_option('logOutputFile', null);
        $dompdf->loadHtml($service->prepararHtml('<p>Boletín de prueba</p><img src="https://colegio.test/firma%20uno.png">'));
        $dompdf->setPaper('folio');
        $dompdf->render();

        $this->assertSame('%PDF-', substr($dompdf->output(), 0, 5));
        $this->assertContains('/Subtype /Image', $dompdf->output());
    }

    public function testConservaImagenesRemotasFaltantesYDatosExistentes()
    {
        $service = new ImagenesBoletinPdf(['https://colegio.test/storage/app/' => $this->directorio]);
        foreach ([
            'https://otro.test/storage/app/firma%20uno.png',
            'https://colegio.test/storage/app/faltante.png',
            'https://colegio.test/storage/app/texto.png',
            'data:image/png;base64,' . $this->png
        ] as $url) {
            $html = "<img src='" . $url . "'>";
            $this->assertSame($html, $service->prepararHtml($html));
        }
    }

    public function testNoLeeImagenesFueraDelDirectorioPermitido()
    {
        mkdir($this->directorio . '/publico');
        $service = new ImagenesBoletinPdf(['https://colegio.test/' => $this->directorio . '/publico']);
        $html = '<img src="https://colegio.test/%2e%2e/firma%20uno.png">';
        $this->assertSame($html, $service->prepararHtml($html));
        rmdir($this->directorio . '/publico');
    }
}
