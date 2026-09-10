<?php

use App\Compras\ComprasDocEncabezado;
use App\Compras\Proveedor;
use App\Compras\Services\CuentaPorPagarService;
use App\Compras\Services\CompraConfirmationService;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\CxP\CxpMovimiento;
use App\CxP\Services\CxpAccountingAccountResolver;
use App\Http\Controllers\Compras\CompraController;
use App\Http\Controllers\Compras\NotaCreditoController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class ComprasCuentaPorPagarDirectaTest extends TestCase
{
    use DatabaseTransactions;

    protected function preparar($tipo = 25)
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        $cuenta = ContabCuenta::where('core_empresa_id', Auth::user()->empresa_id)->where('estado','Activo')->firstOrFail()->replicate();
        $cuenta->codigo = '299999991';
        $cuenta->descripcion = 'Cuenta directa de prueba';
        $cuenta->save();
        $proveedor = Proveedor::firstOrFail();
        $doc = ComprasDocEncabezado::create([
            'core_empresa_id'=>Auth::user()->empresa_id, 'core_tipo_transaccion_id'=>$tipo,
            'core_tipo_doc_app_id'=>App\Core\TipoDocApp::where('estado','Activo')->firstOrFail()->id,
            'consecutivo'=>987654310, 'core_tercero_id'=>$proveedor->core_tercero_id,
            'proveedor_id'=>$proveedor->id, 'fecha'=>'2026-09-10', 'fecha_vencimiento'=>'2026-10-10',
            'forma_pago'=>'credito', 'cta_x_pagar_id'=>$cuenta->id, 'estado'=>'Activo',
            'entrada_almacen_id'=>'', 'descripcion'=>'Compra de prueba', 'valor_total'=>500,
            'creado_por'=>Auth::user()->email, 'modificado_por'=>'',
        ]);
        return [$doc, $cuenta];
    }

    protected function contabilizar($doc)
    {
        CompraController::contabilizar_movimiento_credito($doc->forma_pago, $doc->toArray(), 500, 'Compra de prueba');
        CompraController::crear_registro_pago($doc->forma_pago, $doc->toArray(), 500, 'Compra de prueba');
    }

    protected function movimientos($doc)
    {
        return ContabMovimiento::where('core_empresa_id',$doc->core_empresa_id)
            ->where('core_tipo_transaccion_id',$doc->core_tipo_transaccion_id)
            ->where('core_tipo_doc_app_id',$doc->core_tipo_doc_app_id)->where('consecutivo',$doc->consecutivo);
    }

    public function test_credito_25_y_48_contabiliza_cuenta_directa_y_el_pago_recupera_la_misma()
    {
        foreach ([25,48] as $tipo) {
            list($doc,$cuenta) = $this->preparar($tipo);
            $this->contabilizar($doc);
            $this->assertEquals($cuenta->id, $doc->fresh()->cta_x_pagar_id);
            $this->assertEquals($cuenta->id, $this->movimientos($doc)->firstOrFail()->contab_cuenta_id);
            $this->assertEquals(-500, $this->movimientos($doc)->sum('valor_credito'));
            $cxp = CxpMovimiento::where('core_tipo_transaccion_id',$tipo)->where('consecutivo',$doc->consecutivo)->firstOrFail();
            $this->assertEquals($cuenta->id, (new CxpAccountingAccountResolver())->getPayableAccountId($cxp));
        }
    }

    public function test_sin_cuenta_directa_conserva_la_resolucion_del_proveedor()
    {
        list($doc,$cuenta) = $this->preparar();
        config(['configuracion.cta_por_pagar_default'=>$cuenta->id]);
        $this->assertEquals(Proveedor::get_cuenta_por_pagar($doc->proveedor_id),
            (new CuentaPorPagarService())->resolver($doc->proveedor_id, null, $doc->core_empresa_id));
    }

    public function test_contado_ignora_la_cuenta_directa_incluso_si_es_enviada()
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        $request = new Request(['forma_pago'=>'contado', 'cta_x_pagar_id'=>999999999]);
        (new CompraCuentaDirectaTestController())->validarCuenta($request);
        $this->assertNull($request->cta_x_pagar_id);
    }

    public function test_cuenta_inactiva_es_rechazada_antes_de_guardar()
    {
        list($doc,$cuenta) = $this->preparar();
        $cuenta->update(['estado'=>'Inactivo']);
        $request = new Request(['forma_pago'=>'credito','cta_x_pagar_id'=>$cuenta->id]);
        $request->headers->set('Accept','application/json');
        $this->setExpectedException(Illuminate\Validation\ValidationException::class);
        (new CompraCuentaDirectaTestController())->validarCuenta($request);
    }

    public function test_cuenta_de_otra_empresa_es_rechazada()
    {
        list($doc,$cuenta) = $this->preparar();
        $this->setExpectedException(InvalidArgumentException::class);
        (new CuentaPorPagarService())->resolver($doc->proveedor_id, $cuenta->id, $doc->core_empresa_id + 1000);
    }

    public function test_confirmacion_conserva_la_cuenta_del_encabezado()
    {
        list($doc,$cuenta) = $this->preparar();
        $request = (new ConfirmacionCuentaDirectaTestService())->requestFrom($doc);
        $this->assertEquals($cuenta->id, $request->cta_x_pagar_id);
    }

    public function test_anulacion_elimina_la_cuenta_directa_y_cxp_conservando_trazabilidad()
    {
        list($doc,$cuenta) = $this->preparar();
        $this->contabilizar($doc);
        // La anulación no debe depender de que la cuenta continúe activa.
        $cuenta->update(['estado'=>'Inactivo']);
        CompraController::anular_factura(new Request(['factura_id'=>$doc->id]));
        $this->assertEquals('Anulado', $doc->fresh()->estado);
        $this->assertEquals($cuenta->id, $doc->fresh()->cta_x_pagar_id);
        $this->assertEquals(0, $this->movimientos($doc)->count());
        $this->assertEquals(0, CxpMovimiento::where('core_tipo_transaccion_id',25)->where('consecutivo',$doc->consecutivo)->count());
    }

    public function test_abonos_impiden_anular_documento_con_cuenta_directa()
    {
        list($doc,$cuenta) = $this->preparar();
        $this->contabilizar($doc);
        DB::table('cxp_abonos')->insert([
            'core_empresa_id'=>$doc->core_empresa_id, 'doc_cxp_transacc_id'=>$doc->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id'=>$doc->core_tipo_doc_app_id, 'doc_cxp_consecutivo'=>$doc->consecutivo, 'abono'=>100,
        ]);
        CompraController::anular_factura(new Request(['factura_id'=>$doc->id]));
        $this->assertEquals('Activo', $doc->fresh()->estado);
        $this->assertEquals($cuenta->id, $this->movimientos($doc)->firstOrFail()->contab_cuenta_id);
    }

    public function test_nota_credito_revierte_la_cuenta_directa_de_la_factura()
    {
        list($doc,$cuenta) = $this->preparar();
        $datos = $doc->toArray();
        $datos['consecutivo']++;
        NotaCreditoController::contabilizar_movimiento_debito('credito', $datos, 100, 'Devolución prueba', $doc);
        $this->assertEquals($cuenta->id, ContabMovimiento::where('consecutivo',$datos['consecutivo'])->firstOrFail()->contab_cuenta_id);
    }
}

class CompraCuentaDirectaTestController extends CompraController
{
    public function validarCuenta(Request $request) { $this->validar_cuenta_por_pagar_directa($request); }
}

class ConfirmacionCuentaDirectaTestService extends CompraConfirmationService
{
    public function requestFrom($doc) { return $this->buildRequestFromDocument($doc); }
}
