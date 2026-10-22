<?php

use App\Http\Controllers\Tesoreria\RecaudoCxcController;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoMedioRecaudo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class RecaudoCxcMedioRecaudoValidationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_falta_medio_tarjeta_debito_regresa_al_formulario_sin_crear_encabezado()
    {
        TesoMedioRecaudo::where('estado', TesoMedioRecaudo::ESTADO_ACTIVO)
            ->update(['estado' => 'Inactivo']);

        $lineasDebito = json_encode([
            [
                'tipo_operacion_id_tarjeta_debito' => 'recaudo-cartera',
                'teso_motivo_id_tarjeta_debito' => '1',
                'banco_id_tarjeta_debito' => '1',
                'valor_tarjeta_debito' => '200'
            ],
            ['valor_tarjeta_debito' => '']
        ]);
        $request = Request::create('/tesoreria/recaudos_cxc', 'POST', [
            'lineas_registros_tarjeta_debito' => $lineasDebito,
            'descripcion' => 'Recaudo que debe conservarse'
        ], [], [], ['HTTP_REFERER' => 'http://localhost/tesoreria/recaudos_cxc/create']);
        $session = app('session')->driver();
        $request->setSession($session);
        app()->instance('request', $request);
        $cantidadInicial = TesoDocEncabezado::count();

        $response = (new RecaudoCxcController())->store($request);

        $this->assertInstanceOf('Illuminate\Http\RedirectResponse', $response);
        $this->assertSame('http://localhost/tesoreria/recaudos_cxc/create', $response->getTargetUrl());
        $this->assertContains('No existe un medio de recaudo activo', $session->get('mensaje_error'));
        $this->assertSame($lineasDebito, $session->getOldInput('lineas_registros_tarjeta_debito'));
        $this->assertSame('Recaudo que debe conservarse', $session->getOldInput('descripcion'));
        $this->assertSame($cantidadInicial, TesoDocEncabezado::count());
    }
}
