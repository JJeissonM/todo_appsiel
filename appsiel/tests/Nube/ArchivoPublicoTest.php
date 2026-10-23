<?php

use App\Http\Controllers\web\NubeController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ArchivoPublicoTest extends TestCase
{
    private $directory;
    private $controller;

    public function setUp()
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir().'/nube-test-'.uniqid();
        mkdir($this->directory);
        $this->controller = new PublicCloudForTest($this->directory);
        file_put_contents($this->directory.'/imagen.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }

    public function tearDown()
    {
        foreach (glob($this->directory.'/*') as $file) unlink($file);
        rmdir($this->directory);
        parent::tearDown();
    }

    public function test_imagen_publica_se_entrega_inline_desde_la_carpeta_de_nube()
    {
        $response = $this->controller->archivo('imagen.png');
        $this->assertEquals('image/png', $response->headers->get('Content-Type'));
        $this->assertContains('inline', $response->headers->get('Content-Disposition'));
        $this->assertEquals(realpath($this->directory.'/imagen.png'), $response->getFile()->getRealPath());
    }

    public function test_archivos_inexistentes_y_rutas_fuera_de_nube_devuelven_404()
    {
        foreach (['no-existe.jpg', '../archivo.php', 'imagen.png/otro'] as $path) {
            try {
                $this->controller->archivo($path);
                $this->fail('Se aceptó la ruta '.$path);
            } catch (NotFoundHttpException $e) {
                $this->assertEquals(404, $e->getStatusCode());
            }
        }
    }

    public function test_enlace_simbolico_no_puede_publicar_un_archivo_externo()
    {
        symlink(__FILE__, $this->directory.'/externo.txt');
        $this->setExpectedException(NotFoundHttpException::class);
        $this->controller->archivo('externo.txt');
    }

    public function test_404_no_redirige_a_otra_url_inexistente()
    {
        $handler = app(Illuminate\Contracts\Debug\ExceptionHandler::class);
        $response = $handler->render(Illuminate\Http\Request::create('/nube/no-existe.jpg'), new NotFoundHttpException);
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Location'));
    }
}

class PublicCloudForTest extends NubeController
{
    private $directory;
    public function __construct($directory) { $this->directory = $directory; }
    protected function getCloudRoot() { return realpath($this->directory); }
}
