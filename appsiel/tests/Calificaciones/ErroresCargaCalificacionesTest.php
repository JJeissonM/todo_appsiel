<?php

use App\Exceptions\Handler;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ErroresCargaCalificacionesTest extends TestCase
{
    public function test_validacion_de_guardado_regresa_al_formulario_con_mensaje_y_datos()
    {
        config(['app.debug' => true, 'session.driver' => 'array']);
        $request = Request::create('/web', 'POST', [
            'titulo' => 'PRUEBA EXTERNA', 'peso' => 20, 'anio' => 2026, 'periodo_id' => 31,
            '_token' => 'privado', 'password' => 'privado'
        ]);
        $formulario = 'http://localhost/web/create?id=2&id_modelo=361';
        $request->headers->set('referer', $formulario);
        $this->app->instance('request', $request);
        $mensaje = 'Los porcentajes de los grupos del mismo alcance no pueden superar 100%.';
        $response = $this->app->make(Handler::class)->render($request, new HttpException(422, $mensaje));

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals($formulario, $response->getTargetUrl());
        $this->assertEquals($mensaje, session('mensaje_error'));
        $this->assertEquals('PRUEBA EXTERNA', session()->getOldInput('titulo'));
        $this->assertEquals(20, session()->getOldInput('peso'));
        $this->assertNull(session()->getOldInput('password'));
        $this->assertNull(session()->getOldInput('_token'));
    }

    public function test_validacion_ajax_devuelve_mensaje_json_aun_con_debug_activo()
    {
        config(['app.debug' => true]);
        $request = Request::create('/calificaciones/calificar2', 'POST');
        $request->headers->set('X-Requested-With', 'XMLHttpRequest');
        $mensaje = 'Configure las agrupaciones de columnas para este año/periodo. Sus porcentajes deben sumar 100%.';
        $handler = $this->app->make(Handler::class);
        $response = $handler->render($request, new HttpException(422, $mensaje));

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertEquals(['message' => $mensaje], json_decode($response->getContent(), true));
        $this->assertContains('application/json', $response->headers->get('Content-Type'));
    }
}
