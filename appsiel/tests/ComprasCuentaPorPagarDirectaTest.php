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

    public function test_nota_directa_descarta_cuenta_de_factura_y_revierte_guardado_fallido()
    {
        list($doc) = $this->preparar(40);
        foreach (['', '0', 999999999] as $cuentaEnviada) {
            $datos = $doc->toArray();
            unset($datos['id']);
            $datos['cta_x_pagar_id'] = $cuentaEnviada;
            $controller = new NotaDirectaGuardadoTestController();
            try {
                $controller->store(new Request($datos));
                $this->fail('Se esperaba el fallo simulado.');
            } catch (RuntimeException $e) {
                $this->assertSame('Fallo posterior a la escritura', $e->getMessage());
            }
            $this->assertNull($controller->cuentaRecibida);
            $this->assertNotNull($controller->documentoCreadoId);
            $this->assertNull(ComprasDocEncabezado::find($controller->documentoCreadoId));
        }
    }

    public function test_nota_devolucion_hereda_cuenta_y_revierte_escrituras()
    {
        list($factura, $cuenta) = $this->preparar();
        foreach ([$cuenta->id, null] as $cuentaFactura) {
            $factura->update(['cta_x_pagar_id'=>$cuentaFactura]);
            $request = new Request($factura->toArray());
            $request->merge(['compras_doc_relacionado_id'=>$factura->id, 'cta_x_pagar_id'=>999999999]);
            $controller = new NotaDevolucionGuardadoTestController();
            try {
                $controller->store($request);
                $this->fail('Se esperaba el fallo simulado.');
            } catch (RuntimeException $e) {
                $this->assertSame('Fallo posterior a la escritura', $e->getMessage());
            }
            $this->assertEquals($cuentaFactura, $controller->cuentaRecibida);
            $this->assertNotNull($controller->documentoCreadoId);
            $this->assertNull(ComprasDocEncabezado::find($controller->documentoCreadoId));
        }
    }

    public function test_nota_valor_hereda_cuenta_y_revierte_encabezado_si_falla()
    {
        list($factura, $cuenta) = $this->preparar();
        $modelo = App\Sistema\Modelo::where('name_space', 'App\\Compras\\NotaCreditoValor')->firstOrFail();
        foreach ([$cuenta->id, null] as $cuentaFactura) {
            $factura->update(['cta_x_pagar_id'=>$cuentaFactura]);
            $request = new Request($factura->toArray());
            $request->merge(['compras_doc_relacionado_id'=>$factura->id, 'cta_x_pagar_id'=>999999999,
                'url_id_modelo'=>$modelo->id, 'core_tipo_transaccion_id'=>61]);
            $controller = new NotaValorGuardadoTestController();
            try {
                $controller->store($request);
                $this->fail('Se esperaba el fallo simulado.');
            } catch (RuntimeException $e) {
                $this->assertSame('Fallo posterior a la escritura', $e->getMessage());
            }
            $this->assertEquals($cuentaFactura, $controller->cuentaRecibida);
            $this->assertNotNull($controller->documentoCreadoId);
            $this->assertNull(ComprasDocEncabezado::find($controller->documentoCreadoId));
        }
    }

    public function test_nota_credito_revierte_la_cuenta_directa_de_la_factura()
    {
        list($doc,$cuenta) = $this->preparar();
        $datos = $doc->toArray();
        $datos['consecutivo']++;
        NotaCreditoController::contabilizar_movimiento_debito('credito', $datos, 100, 'Devolución prueba', $doc);
        $this->assertEquals($cuenta->id, ContabMovimiento::where('consecutivo',$datos['consecutivo'])->firstOrFail()->contab_cuenta_id);
    }

    public function test_nota_credito_no_puede_superar_saldo_pendiente_de_factura()
    {
        list($factura) = $this->preparar();
        $this->contabilizar($factura);
        $controller = new NotaDevolucionGuardadoTestController();

        // Se permite devolver exactamente el saldo disponible.
        $controller->validarSaldoPendiente($factura, 500);

        $this->setExpectedException(
            InvalidArgumentException::class,
            'supera el saldo pendiente por pagar de la factura'
        );
        $controller->validarSaldoPendiente($factura, 500.01);
    }

    public function test_create_nota_credito_envia_por_ajax_y_conserva_formulario_ante_error()
    {
        $vista = file_get_contents(resource_path('views/compras/notas_credito/create.blade.php'));

        $this->assertContains("$('#form_create').on('submit'", $vista);
        $this->assertContains('$.ajax({', $vista);
        $this->assertContains("formulario.serialize()", $vista);
        $this->assertContains("xhr.responseJSON.message", $vista);
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

class NotaDirectaGuardadoTestController extends App\Http\Controllers\Compras\NotaCreditoDirectaController
{
    public $cuentaRecibida;
    public $documentoCreadoId;

    public function crear_devolucion(Request $request)
    {
        $this->cuentaRecibida = $request->input('cta_x_pagar_id');
        // Escribir con la FK real para reproducir el error del encabezado.
        $doc = App\Compras\NotaCreditoDirecta::create($request->all());
        $this->documentoCreadoId = $doc->id;
        throw new RuntimeException('Fallo posterior a la escritura');
    }
}

class NotaDevolucionGuardadoTestController extends NotaCreditoController
{
    public $cuentaRecibida;
    public $documentoCreadoId;

    public function crear_devolucion(Request $request, $entrada_almacen_id)
    {
        $this->cuentaRecibida = $request->input('cta_x_pagar_id');
        $doc = App\Compras\NotaCredito::create($request->all());
        $this->documentoCreadoId = $doc->id;
        throw new RuntimeException('Fallo posterior a la escritura');
    }

    public function validarSaldoPendiente($factura, $totalNota)
    {
        return $this->validar_saldo_pendiente_factura($factura, $totalNota);
    }
}

class NotaValorGuardadoTestController extends App\Http\Controllers\Compras\NotaCreditoValorController
{
    public $cuentaRecibida;
    public $documentoCreadoId;

    public function crear_registros_nota_credito(Request $request, $nota_credito, $factura)
    {
        $this->cuentaRecibida = $nota_credito->cta_x_pagar_id;
        $this->documentoCreadoId = $nota_credito->id;
        throw new RuntimeException('Fallo posterior a la escritura');
    }
}
