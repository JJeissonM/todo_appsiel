<?php

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasRetencionLiquidacion;
use App\Compras\Services\ReteicaService;
use App\Compras\Services\ContabilidadService;
use App\Contabilidad\CategoriaRetencion;
use App\Contabilidad\Retencion;
use App\Contabilidad\RegistroRetencion;
use App\Contabilidad\ContabMovimiento;
use App\Contabilidad\ContabCuenta;
use App\Core\Tercero;
use App\CxP\CxpMovimiento;
use App\Http\Controllers\Compras\CompraController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;

class ComprasReteicaTest extends TestCase
{
    use DatabaseTransactions;

    protected function preparar($tipo = 25)
    {
        $this->be(App\User::whereNotNull('empresa_id')->firstOrFail());
        $categoria = CategoriaRetencion::create(['descripcion'=>'ICA test', 'nombre_corto'=>'ICA test', 'estado'=>'Activo']);
        $tercero = Tercero::where('estado', 'Activo')->firstOrFail();
        config(['compras.maneja_retenciones_fuente'=>1, 'contabilidad.categoria_reteica_id'=>$categoria->id, 'contabilidad.tercero_reteica_id'=>$tercero->id]);
        $retencion = Retencion::create([
            'categoria_retenciones_id'=>$categoria->id, 'descripcion'=>'ReteICA prueba', 'nombre_corto'=>'ICA test',
            'tasa_retencion'=>0.414, 'cta_compras_id'=>ContabCuenta::where('estado','Activo')->firstOrFail()->id,
            'cta_compras_devol_id'=>0, 'cta_ventas_id'=>0, 'cta_ventas_devol_id'=>0, 'estado'=>'Activo'
        ]);
        $doc = new ComprasDocEncabezado([
            'core_empresa_id'=>Auth::user()->empresa_id, 'core_tipo_transaccion_id'=>$tipo,
            'core_tipo_doc_app_id'=>1, 'consecutivo'=>987654321, 'core_tercero_id'=>$tercero->id,
            'fecha'=>'2026-09-10', 'fecha_vencimiento'=>'2026-10-10', 'forma_pago'=>'credito',
            'estado'=>'Activo', 'creado_por'=>Auth::user()->email, 'modificado_por'=>'',
            'reteica_retencion_id'=>$retencion->id, 'entrada_almacen_id'=>'', 'proveedor_id'=>1,
            'descripcion'=>'Prueba de ReteICA', 'valor_total'=>5355000,
        ]);
        $doc->save();
        ComprasDocRegistro::create([
            'compras_doc_encabezado_id'=>$doc->id, 'base_impuesto'=>4500000, 'precio_total'=>5355000,
            'precio_unitario'=>5355000, 'cantidad'=>1, 'tasa_impuesto'=>19, 'valor_impuesto'=>855000,
            'estado'=>'Activo', 'creado_por'=>Auth::user()->email
        ]);
        return [$doc, $retencion];
    }

    public function test_calculo_por_mil_y_redondeo()
    {
        $s = new ReteicaService();
        $this->assertSame(18630.0, $s->calcular(4500000, .414));
        $this->assertSame(0.0, $s->calcular(0, .414));
        $this->assertSame(1.24, $s->calcular(300.01, .414));
    }

    public function test_base_invalida_se_rechaza()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        (new ReteicaService())->calcular(-1, .414);
    }

    public function test_categoria_ajena_se_rechaza()
    {
        list($doc, $retencion) = $this->preparar();
        config(['contabilidad.categoria_reteica_id'=>0]);
        $this->setExpectedException(InvalidArgumentException::class);
        (new ReteicaService())->validar_seleccion($retencion->id, 25);
    }

    public function test_contabiliza_25_y_48_sin_duplicar_y_con_trazabilidad()
    {
        foreach ([25, 48] as $tipo) {
            list($doc, $retencion) = $this->preparar($tipo);
            $service = new ReteicaService();
            $this->assertSame(18630.0, $service->liquidar_documento($doc));
            $service->contabilizar($doc);
            $service->contabilizar($doc);
            $registros = (new ContabilidadService())->get_retenciones($doc);
            $this->assertCount(1, $registros);
            $this->assertEquals(18630, $registros->sum('valor'));
            $this->assertEquals(4500000, $registros->first()->valor_base_retencion);
            $this->assertEquals(.414, $registros->first()->tasa_retencion);
            $this->assertEquals(1, ComprasRetencionLiquidacion::where('compras_doc_encabezado_id',$doc->id)->count());
            $this->assertEquals(-18630, ContabMovimiento::where('core_tipo_transaccion_id',$tipo)->where('consecutivo',$doc->consecutivo)->sum('valor_credito'));
            $this->assertEquals(18630, CxpMovimiento::where('core_tipo_transaccion_id',$tipo)->where('consecutivo',$doc->consecutivo)->sum('saldo_pendiente'));
        }
    }

    public function test_anulacion_inactiva_retencion_y_liquidacion_y_elimina_obligacion()
    {
        list($doc) = $this->preparar();
        $service = new ReteicaService();
        $service->liquidar_documento($doc);
        $service->contabilizar($doc);
        CompraController::anular_factura(new Request(['factura_id'=>$doc->id]));
        $this->assertEquals('Anulado', $doc->fresh()->estado);
        $this->assertEquals(0, (new ContabilidadService())->get_valor_retenciones($doc));
        $liq = ComprasRetencionLiquidacion::where('compras_doc_encabezado_id',$doc->id)->firstOrFail();
        $this->assertEquals('Anulado', $liq->estado);
        $this->assertEquals(0, $liq->aplicada);
        $this->assertEquals('Anulado', RegistroRetencion::find($liq->contab_registro_retencion_id)->estado);
        $this->assertEquals(0, CxpMovimiento::where('core_tipo_transaccion_id',25)->where('consecutivo',$doc->consecutivo)->count());
        $this->assertEquals(0, ContabMovimiento::where('core_tipo_transaccion_id',25)->where('consecutivo',$doc->consecutivo)->count());
        $this->setExpectedException(InvalidArgumentException::class);
        $service->contabilizar($doc->fresh());
    }

    public function test_seeder_es_idempotente_y_no_modifica_cuentas()
    {
        require_once base_path('database/seeds/ComprasReteicaValledupar2026Seeder.php');
        $seeder = new ComprasReteicaValledupar2026Seeder();
        $seeder->run();
        $count = Retencion::count();
        $retencion = Retencion::where('descripcion','like','ReteICA Valledupar 2026%')->firstOrFail();
        $retencion->cta_compras_id = 123;
        $retencion->save();
        $seeder->run();
        $this->assertEquals($count, Retencion::count());
        $this->assertEquals(123, $retencion->fresh()->cta_compras_id);
    }
    public function test_cuenta_o_recaudador_sin_configurar_impiden_contabilizar()
    {
        list($doc, $retencion) = $this->preparar();
        $retencion->cta_compras_id = 0;
        $retencion->save();
        $this->setExpectedException(InvalidArgumentException::class);
        (new ReteicaService())->liquidar_documento($doc);
    }

    public function test_catalogo_excluye_inactivas_y_retefuente_excluye_ica()
    {
        list($doc, $retencion) = $this->preparar();
        config(['compras.maneja_retenciones_fuente'=>1]);
        $this->assertCount(1, (new ReteicaService())->retenciones_activas());
        $this->assertFalse((new App\Compras\Services\RetencionFuenteService())->get_retenciones_activas()->contains('id', $retencion->id));
        $retencion->estado = 'Inactivo';
        $retencion->save();
        $this->assertCount(0, (new ReteicaService())->retenciones_activas());
    }

    public function test_anulacion_con_abonos_no_modifica_retenciones()
    {
        list($doc) = $this->preparar();
        $service = new ReteicaService();
        $service->liquidar_documento($doc);
        $service->contabilizar($doc);
        DB::table('cxp_abonos')->insert([
            'core_empresa_id'=>$doc->core_empresa_id, 'doc_cxp_transacc_id'=>$doc->core_tipo_transaccion_id,
            'doc_cxp_tipo_doc_id'=>$doc->core_tipo_doc_app_id, 'doc_cxp_consecutivo'=>$doc->consecutivo, 'abono'=>1
        ]);
        CompraController::anular_factura(new Request(['factura_id'=>$doc->id]));
        $this->assertEquals('Activo', $doc->fresh()->estado);
        $this->assertEquals(18630, (new ContabilidadService())->get_valor_retenciones($doc));
    }

    public function test_base_se_recalcula_y_no_incluye_lineas_anuladas()
    {
        list($doc) = $this->preparar();
        ComprasDocRegistro::create(['compras_doc_encabezado_id'=>$doc->id, 'base_impuesto'=>9000000, 'estado'=>'Anulado']);
        $doc->reteica_valor = 1;
        $this->assertEquals(18630, (new ReteicaService())->liquidar_documento($doc));
        $this->assertEquals(18630, $doc->fresh()->reteica_valor);
    }

    public function test_vista_expone_controles_y_tarifas_solo_para_25_y_48()
    {
        list($doc, $retencion) = $this->preparar();
        foreach ([25, 48] as $tipo) {
            Input::replace(['id_transaccion'=>$tipo]);
            $html = view('compras.incluir.reteica')->render();
            $this->assertContains('4.14 por mil', $html);
            foreach (['reteica_add','reteica_confirmar','reteica_editar','reteica_reset'] as $id) {
                $this->assertContains('id="'.$id.'"', $html);
            }
        }
        Input::replace(['id_transaccion'=>24]);
        $this->assertSame('', trim(view('compras.incluir.reteica')->render()));
    }

    public function test_retefuente_y_reteica_cuadran_con_pago_neto_contado_y_credito()
    {
        foreach (['credito', 'contado'] as $forma) {
            list($doc, $ica) = $this->preparar($forma == 'credito' ? 25 : 48);
            $doc->forma_pago = $forma;
            $doc->save();
            config(['compras.maneja_retenciones_fuente'=>1, 'contabilidad.tercero_dian_id'=>$doc->core_tercero_id]);
            $categoria = CategoriaRetencion::create(['descripcion'=>'Fuente test', 'nombre_corto'=>'Fuente test', 'estado'=>'Activo']);
            $fuente = $ica->replicate();
            $fuente->categoria_retenciones_id = $categoria->id;
            $fuente->tasa_retencion = 2.5;
            $fuente->save();
            $linea = $doc->lineas_registros()->first();
            $linea->contab_retencion_id = $fuente->id;
            $linea->tasa_retencion = 2.5;
            $linea->valor_retencion = 112500;
            $linea->save();
            $service = new ReteicaService();
            $neto = 5355000 - 112500 - $service->liquidar_documento($doc);
            $datos = $doc->toArray();
            $datos['registros_medio_pago'] = [];
            (new ContabMovimiento())->contabilizar_linea_registro($datos, $ica->cta_compras_id, 'Compra prueba', 5355000, 0);
            CompraController::contabilizar_movimiento_credito($forma, $datos, $neto, 'Compra prueba');
            CompraController::crear_registro_pago($forma, $datos, $neto, 'Compra prueba');
            (new ContabilidadService())->aplicar_retenciones_por_linea_compras($doc);
            $service->contabilizar($doc);
            $this->assertEquals(131130, (new ContabilidadService())->get_valor_retenciones($doc));
            $this->assertEquals(0, ContabMovimiento::where('core_tipo_transaccion_id',$doc->core_tipo_transaccion_id)
                ->where('consecutivo',$doc->consecutivo)->sum('valor_saldo'));
            $this->assertEquals(5223870, $neto);
        }
    }

    public function test_config_inactiva_oculta_reteica_y_rechaza_su_aplicacion()
    {
        list($doc, $retencion) = $this->preparar();
        config(['compras.maneja_retenciones_fuente'=>'0']);
        foreach ([25, 48] as $tipo) {
            Input::replace(['id_transaccion'=>$tipo]);
            $this->assertSame('', trim(view('compras.incluir.reteica')->render()));
        }
        $service = new ReteicaService();
        $this->assertCount(0, $service->retenciones_activas());
        $this->assertNull($service->validar_seleccion(0, 25));
        $this->setExpectedException(InvalidArgumentException::class);
        $service->liquidar_documento($doc);
    }

    public function test_reteica_requiere_config_explicitamente_activa()
    {
        $service = new ReteicaService();
        foreach ([0, '0', false, null, '', 'No', 'false', 'Inactivo', 'automatico'] as $valor) {
            config(['compras.maneja_retenciones_fuente'=>$valor]);
            $this->assertFalse($service->habilitado());
        }
        foreach ([1, '1', true, 'Si', 'sí', 'true', 'Activo', 'habilitado'] as $valor) {
            config(['compras.maneja_retenciones_fuente'=>$valor]);
            $this->assertTrue($service->habilitado());
        }
    }

}
