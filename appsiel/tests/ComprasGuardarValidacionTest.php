<?php

use App\Http\Controllers\Compras\CompraController;
use App\Http\Controllers\Compras\FacturaEntradaPendienteController;
use Illuminate\Http\Request;

class ComprasGuardarValidacionTest extends TestCase
{
    public function test_bodega_invalida_devuelve_error_json_sin_redireccion()
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        $request = Request::create('/compras', 'POST', ['inv_bodega_id'=>999999999, 'reteica_retencion_id'=>0]);
        $request->headers->set('Accept', 'application/json');
        $response = app(CompraController::class)->store($request);
        $this->assertEquals(422, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Location'));
        $this->assertContains('Debe seleccionar una bodega activa', $response->getContent());
    }

    public function test_reteica_invalida_devuelve_error_json_en_ambos_guardados()
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        config(['compras.maneja_retenciones_fuente'=>0]);
        foreach ([CompraController::class, FacturaEntradaPendienteController::class] as $controller) {
            $request = Request::create('/compras', 'POST', ['reteica_retencion_id'=>1, 'core_tipo_transaccion_id'=>25]);
            $request->headers->set('Accept', 'application/json');
            $response = app($controller)->store($request);
            $this->assertEquals(422, $response->getStatusCode());
            $this->assertFalse($response->headers->has('Location'));
            $this->assertContains('activar el manejo de retenciones', $response->getContent());
        }
    }
    public function test_documento_de_entrada_sin_configurar_no_crea_encabezados_ni_consecutivos()
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        $bodega = App\Inventarios\InvBodega::where('core_empresa_id', Auth::user()->empresa_id)
            ->where('estado', 'Activo')->firstOrFail();
        $encabezados = App\Inventarios\InvDocEncabezado::count();
        $consecutivos = App\Core\ConsecutivoDocumento::count();
        foreach (['', 0, 999999999] as $tipo) {
            config(['compras.ea_tipo_doc_app_id'=>$tipo]);
            $request = Request::create('/compras', 'POST', ['inv_bodega_id'=>$bodega->id, 'reteica_retencion_id'=>0]);
            $request->headers->set('Accept', 'application/json');
            $response = app(CompraController::class)->store($request);
            $this->assertEquals(422, $response->getStatusCode());
            $this->assertContains('Documento para entradas de almacén activo', json_decode($response->getContent(), true)['message']);
            $this->assertFalse($response->headers->has('Location'));
        }
        $this->assertEquals($encabezados, App\Inventarios\InvDocEncabezado::count());
        $this->assertEquals($consecutivos, App\Core\ConsecutivoDocumento::count());
    }

    public function test_documento_de_entrada_activo_es_aceptado()
    {
        $tipo = App\Core\TipoDocApp::where('estado', 'Activo')->firstOrFail();
        config(['compras.ea_tipo_doc_app_id'=>$tipo->id]);
        $this->assertEquals($tipo->id, app(CompraController::class)->validar_documento_entrada_almacen());
    }

}
