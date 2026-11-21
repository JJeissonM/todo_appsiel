<?php

use App\Compras\ComprasDocEncabezado;
use App\Compras\ComprasDocRegistro;
use App\Compras\ComprasMovimiento;
use App\Compras\Services\CompraConfirmationService;
use App\Compras\Services\CompraConfirmationPaymentService;
use App\Contabilidad\ContabCuenta;
use App\Contabilidad\ContabMovimiento;
use App\Inventarios\InvDocEncabezado;
use App\Inventarios\InvDocRegistro;
use App\Inventarios\InvMovimiento;
use App\Tesoreria\TesoMovimiento;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasBotConfirmationTest extends TestCase
{
    use DatabaseTransactions;

    protected $company;
    protected $box;
    protected $bank;
    protected $cashMedium;
    protected $bankMedium;
    protected $motive;
    protected $provider;
    protected $product;
    protected $payable;
    protected $cashAccount;
    protected $bankAccount;

    public function setUp()
    {
        parent::setUp();
        // Requiere una copia aislada de los maestros: nunca ejecutar sobre una base de usuario.
        if (strpos(DB::connection()->getDatabaseName(), 'codex_confirmation_') !== 0) {
            $this->markTestSkipped('Usar una copia local aislada codex_confirmation_*.');
        }
        $user = App\User::whereNotNull('empresa_id')->firstOrFail();
        $this->be($user); $this->company = $user->empresa_id;
        config(['configuracion.liquidacion_impuestos'=>1,'compras.maneja_retenciones_fuente'=>0,
            'compras.ocultar_cantidad_lineas'=>0,'compras.ea_tipo_transaccion_id'=>35,'compras.ea_modelo_id'=>165,'inventarios.maneja_costo_promedio_por_bodegas'=>0]);
        $this->cashAccount = $this->account('Caja prueba'); $this->bankAccount = $this->account('Banco prueba');
        $this->payable = $this->account('CxP prueba'); $inventoryAccount = $this->account('Inventario prueba');
        $this->box = App\Tesoreria\TesoCaja::where('core_empresa_id',$this->company)->firstOrFail()->replicate();
        $this->box->contab_cuenta_id = $this->cashAccount->id; $this->box->estado='Activo'; $this->box->save();
        $this->bank = App\Tesoreria\TesoCuentaBancaria::where('core_empresa_id',$this->company)->firstOrFail()->replicate();
        $this->bank->contab_cuenta_id=$this->bankAccount->id; $this->bank->estado='Activo'; $this->bank->save();
        $this->cashMedium=App\Tesoreria\TesoMedioRecaudo::create(['descripcion'=>'Efectivo prueba','comportamiento'=>'Efectivo','estado'=>'Activo']);
        $this->bankMedium=App\Tesoreria\TesoMedioRecaudo::create(['descripcion'=>'Banco prueba','comportamiento'=>'Tarjeta bancaria','estado'=>'Activo']);
        if(Schema::hasTable('teso_medios_recaudo_destinos')) {
            App\Tesoreria\TesoMedioRecaudoDestino::create(['teso_medio_recaudo_id'=>$this->cashMedium->id,'teso_caja_id'=>$this->box->id,'teso_cuenta_bancaria_id'=>null,'estado'=>'Activo']);
            App\Tesoreria\TesoMedioRecaudoDestino::create(['teso_medio_recaudo_id'=>$this->bankMedium->id,'teso_caja_id'=>null,'teso_cuenta_bancaria_id'=>$this->bank->id,'estado'=>'Activo']);
        }
        $this->motive=App\Tesoreria\TesoMotivo::where('core_empresa_id',$this->company)->where('movimiento','salida')->firstOrFail();
        config(['tesoreria.motivo_tesoreria_compras_contado' => $this->motive->id]);
        $this->provider=App\Compras\Proveedor::where('estado','Activo')->firstOrFail()->replicate();
        $this->provider->inv_bodega_id=App\Inventarios\InvBodega::where('core_empresa_id',$this->company)->where('estado','Activo')->firstOrFail()->id;
        $this->provider->liquida_impuestos=1;$this->provider->save();
        $group=App\Inventarios\InvGrupo::where('core_empresa_id',$this->company)->firstOrFail()->replicate();$group->cta_inventarios_id=$inventoryAccount->id;$group->save();
        $tax=App\Contabilidad\Impuesto::firstOrFail()->replicate();$tax->tasa_impuesto=19;$tax->cta_compras_id=$this->account('IVA descontable prueba')->id;$tax->save();
        $this->product=App\Inventarios\InvProducto::where('core_empresa_id',$this->company)->firstOrFail()->replicate();
        $this->product->inv_grupo_id=$group->id;$this->product->impuesto_id=$tax->id;$this->product->tipo='servicio';$this->product->estado='Activo';$this->product->save();
    }

    protected function account($label)
    {
        $account=ContabCuenta::where('core_empresa_id',$this->company)->where('estado','Activo')->firstOrFail()->replicate();
        $account->codigo='999'.mt_rand(100000,999999);$account->descripcion=$label;$account->save();return $account;
    }

    protected function invoice($payment='contado',$retention=0)
    {
        $doc=ComprasDocEncabezado::create(['core_empresa_id'=>$this->company,'core_tipo_transaccion_id'=>25,
            'core_tipo_doc_app_id'=>App\Core\TipoDocApp::where('estado','Activo')->firstOrFail()->id,
            'consecutivo'=>99999001,'fecha'=>'2026-11-21','fecha_recepcion'=>'2026-11-21','fecha_vencimiento'=>'2026-12-21',
            'core_tercero_id'=>$this->provider->core_tercero_id,'proveedor_id'=>$this->provider->id,'forma_pago'=>$payment,
            'cta_x_pagar_id'=>$payment==='credito'?$this->payable->id:null,'sincronizado_bot'=>1,'valor_total'=>999999,
            'estado'=>'Activo','descripcion'=>'Factura importada de prueba','creado_por'=>Auth::user()->email,
            'modificado_por'=>'','entrada_almacen_id'=>'','doc_proveedor_prefijo'=>'BOT','doc_proveedor_consecutivo'=>'PRUEBA',
            'cotizacion_id'=>0,'compras_doc_relacionado_id'=>0,'comprador_id'=>0]);
        ComprasDocRegistro::create(['compras_doc_encabezado_id'=>$doc->id,'inv_producto_id'=>$this->product->id,'inv_motivo_id'=>0,
            'precio_unitario'=>59.5,'cantidad'=>2,'precio_total'=>119,'base_impuesto'=>100,'tasa_impuesto'=>19,'valor_impuesto'=>19,
            'tasa_descuento'=>0,'valor_total_descuento'=>0,'cantidad_recibida'=>0,'cantidad_devuelta'=>0,
            'contab_retencion_id'=>0,'valor_retencion'=>$retention,'tasa_retencion'=>0,'base_retencion'=>null,
            'estado'=>'Activo','creado_por'=>Auth::user()->email,'modificado_por'=>'']);
        return $doc;
    }

    protected function payments($cash=40,$bank=79)
    {
        return [
            ['teso_medio_recaudo_id'=>$this->cashMedium->id,'teso_motivo_id'=>$this->motive->id,'teso_caja_id'=>$this->box->id,'teso_cuenta_bancaria_id'=>0,'valor_recaudo'=>$cash],
            ['teso_medio_recaudo_id'=>$this->bankMedium->id,'teso_motivo_id'=>$this->motive->id,'teso_caja_id'=>0,'teso_cuenta_bancaria_id'=>$this->bank->id,'valor_recaudo'=>$bank]
        ];
    }

    protected function key($doc)
    {
        return ['core_empresa_id'=>$doc->core_empresa_id,'core_tipo_transaccion_id'=>$doc->core_tipo_transaccion_id,
            'core_tipo_doc_app_id'=>$doc->core_tipo_doc_app_id,'consecutivo'=>$doc->consecutivo];
    }

    public function test_contado_mixto_genera_entrada_compra_contabilidad_y_dos_pagos()
    {
        $doc=$this->invoice();$service=new CompraConfirmationService();$service->confirm($doc,$this->payments());$doc=$doc->fresh();
        $this->assertEquals(119,$doc->valor_total);$this->assertNotEmpty($doc->entrada_almacen_id);
        $this->assertEquals(1,InvDocRegistro::where('inv_doc_encabezado_id',$doc->entrada_almacen_id)->count());
        $this->assertEquals(100,InvDocRegistro::where('inv_doc_encabezado_id',$doc->entrada_almacen_id)->sum('costo_total'));
        $this->assertEquals(1,ComprasMovimiento::where($this->key($doc))->count());
        $this->assertEquals(2,TesoMovimiento::where($this->key($doc))->count());
        $this->assertEquals(-119,TesoMovimiento::where($this->key($doc))->sum('valor_movimiento'));
        $this->assertEquals(2,TesoMovimiento::where($this->key($doc))->where('teso_motivo_id',$this->motive->id)->count());
        $this->assertEquals(119,ContabMovimiento::where($this->key($doc))->sum('valor_debito'));
        $this->assertEquals(-119,ContabMovimiento::where($this->key($doc))->sum('valor_credito'));
        $bank=ContabMovimiento::where($this->key($doc))->where('contab_cuenta_id',$this->bankAccount->id)->firstOrFail();
        $this->assertEquals(-79,$bank->valor_credito);$this->assertEquals($this->bank->id,$bank->teso_cuenta_bancaria_id);
        $this->assertEquals(0,App\CxP\CxpMovimiento::where($this->key($doc))->count());
        $before=TesoMovimiento::where($this->key($doc))->count();
        try{$service->confirm($doc,$this->payments());$this->fail('Debió rechazar confirmación repetida');}catch(Exception $e){$this->assertContains('ya tiene movimientos',$e->getMessage());}
        $this->assertEquals($before,TesoMovimiento::where($this->key($doc))->count());
    }

    public function test_servicio_confirmado_con_dos_pagos_no_genera_movimientos_de_inventario()
    {
        $this->assertSame('servicio', $this->product->tipo);
        $doc = $this->invoice();
        $movimientos = InvMovimiento::count();
        (new CompraConfirmationService())->confirm($doc, $this->payments());
        $this->assertEquals($movimientos, InvMovimiento::count());
        $this->assertEquals(2, TesoMovimiento::where($this->key($doc))->count());
        $this->assertEquals(1, ComprasMovimiento::where($this->key($doc))->count());
    }

    public function test_item_sin_mapear_informa_la_linea_y_no_genera_entrada_ni_pagos()
    {
        $doc = $this->invoice();
        $linea = $doc->lineas_registros()->first();
        $linea->inv_producto_id = 0;
        $linea->save();
        $entradas = InvDocEncabezado::count();
        try {
            (new CompraConfirmationService())->confirm($doc, $this->payments());
            $this->fail('Debe exigir asociar el producto o servicio.');
        } catch (Exception $e) {
            $this->assertContains('La línea ' . $linea->id . ' está pendiente de mapear', $e->getMessage());
        }
        $this->assertEquals($entradas, InvDocEncabezado::count());
        $this->assertEquals(0, TesoMovimiento::where($this->key($doc))->count());
    }

    public function test_confirmacion_impone_motivo_configurado_aunque_el_cliente_envie_otro()
    {
        $doc = $this->invoice();
        $pagos = $this->payments();
        foreach ($pagos as &$pago) $pago['teso_motivo_id'] = 999999;
        unset($pago);
        (new CompraConfirmationService())->confirm($doc, $pagos);
        $this->assertEquals(2, TesoMovimiento::where($this->key($doc))
            ->where('teso_motivo_id', $this->motive->id)->count());
    }

    public function test_motivo_configurado_invalido_impide_confirmar_sin_crear_entrada()
    {
        config(['tesoreria.motivo_tesoreria_compras_contado' => 0]);
        $doc = $this->invoice();
        $entradas = InvDocEncabezado::count();
        try {
            (new CompraConfirmationService())->confirm($doc, $this->payments());
            $this->fail('Debe exigir el motivo configurado.');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('Configure un motivo activo', $e->getMessage());
        }
        $this->assertEquals($entradas, InvDocEncabezado::count());
    }

    public function test_cufe_duplicado_preexistente_impide_generar_entrada()
    {
        $doc = $this->invoice();
        $doc->cufe = 'cufe-duplicado-preexistente';
        $doc->sincronizado_bot = true;
        $doc->save();
        $otro = $doc->replicate();
        $otro->consecutivo++;
        $otro->save();
        $entradas = InvDocEncabezado::count();
        try {
            (new CompraConfirmationService())->confirm($doc, $this->payments());
            $this->fail('Debe rechazar un CUFE duplicado.');
        } catch (Exception $e) {
            $this->assertContains('mismo CUFE', $e->getMessage());
        }
        $this->assertEquals($entradas, InvDocEncabezado::count());
        $this->assertEmpty($doc->fresh()->entrada_almacen_id);
    }

    public function test_reimportacion_de_cufe_existente_sin_log_no_duplica_factura_ni_entrada()
    {
        $doc = $this->invoice();
        $doc->cufe = 'bot-cufe-prueba-antiduplicado';
        $doc->save();
        (new CompraConfirmationService())->confirm($doc, $this->payments());
        $facturas = ComprasDocEncabezado::count();
        $entradas = InvDocEncabezado::count();
        $payload = ['data' => [
            ['cufe' => $doc->cufe, 'invoice_data' => ['invoice' => []]],
            ['cufe' => $doc->cufe, 'invoice_data' => ['invoice' => []]],
        ]];
        $resultado = (new App\Compras\Services\SyncFacturaCompraService())->sincronizar(
            $payload, $this->company, Auth::user()->email
        );
        $this->assertEquals(2, $resultado['duplicadas']);
        $this->assertEquals(0, $resultado['fallidas']);
        $this->assertEquals($facturas, ComprasDocEncabezado::count());
        $this->assertEquals($entradas, InvDocEncabezado::count());
        try {
            (new CompraConfirmationService())->confirm($doc, $this->payments());
            $this->fail('Debe rechazar una segunda confirmación.');
        } catch (Exception $e) {
            $this->assertContains('ya tiene movimientos', $e->getMessage());
        }
        $this->assertEquals($entradas, InvDocEncabezado::count());
    }

    public function test_producto_genera_existencia_y_asientos_de_entrada_balanceados()
    {
        $this->product->tipo='producto';$this->product->save();$doc=$this->invoice();
        (new CompraConfirmationService())->confirm($doc,$this->payments());$doc=$doc->fresh();
        $this->assertEquals(2,InvMovimiento::where('inv_doc_encabezado_id',$doc->entrada_almacen_id)->sum('cantidad'));
        $this->assertEquals(100,InvMovimiento::where('inv_doc_encabezado_id',$doc->entrada_almacen_id)->sum('costo_total'));
        $entry=InvDocEncabezado::findOrFail($doc->entrada_almacen_id);
        $this->assertEquals(0,ContabMovimiento::where($this->key($entry))->sum('valor_saldo'),'',0.01);
    }

    public function test_confirmacion_y_store_generan_los_mismos_importes_y_cuentas()
    {
        $doc=$this->invoice();(new CompraConfirmationService())->confirm($doc,$this->payments());$doc=$doc->fresh();
        $motivo=App\Inventarios\InvMotivo::where('core_empresa_id',$this->company)->where('core_tipo_transaccion_id',35)
            ->where('estado','Activo')->where('movimiento','entrada')->orderBy('id')->firstOrFail();
        $pagos=[];
        foreach($this->payments() as $p)$pagos[]=[
            'teso_medio_recaudo_id'=>$p['teso_medio_recaudo_id'].'-Medio','teso_motivo_id'=>$p['teso_motivo_id'].'-Motivo',
            'teso_caja_id'=>$p['teso_caja_id'].'-Caja','teso_cuenta_bancaria_id'=>$p['teso_cuenta_bancaria_id'].'-Banco','valor'=>'$'.$p['valor_recaudo']];
        $pagos[]=['teso_medio_recaudo_id'=>'','valor'=>'$119'];
        $line=['inv_motivo_id'=>$motivo->id,'inv_producto_id'=>$this->product->id,'inv_bodega_id'=>$this->provider->inv_bodega_id,
            'cantidad'=>2,'costo_unitario'=>50,'costo_total'=>100,'precio_unitario'=>59.5,'precio_total'=>119,
            'base_impuesto'=>100,'tasa_impuesto'=>19,'valor_impuesto'=>19,'tasa_descuento'=>0,'valor_total_descuento'=>0,
            'contab_retencion_id'=>0,'tasa_retencion'=>0,'valor_retencion'=>0];
        $request=new Illuminate\Http\Request([
            'core_empresa_id'=>$this->company,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>$doc->core_tipo_doc_app_id,
            'core_tercero_id'=>$this->provider->core_tercero_id,'proveedor_id'=>$this->provider->id,'comprador_id'=>0,
            'clase_proveedor_id'=>$this->provider->clase_proveedor_id,'forma_pago'=>'contado','liquida_impuestos'=>1,
            'inv_bodega_id'=>$this->provider->inv_bodega_id,'fecha'=>'2026-11-21','fecha_recepcion'=>'2026-11-21','fecha_vencimiento'=>'2026-12-21',
            'descripcion'=>'Store comparación Bot','doc_proveedor_prefijo'=>'PRUEBA','doc_proveedor_consecutivo'=>'STORE',
            'estado'=>'Activo','creado_por'=>Auth::user()->email,'modificado_por'=>'','cotizacion_id'=>0,'compras_doc_relacionado_id'=>0,
            'url_id'=>9,'url_id_modelo'=>159,'url_id_transaccion'=>25,'reteica_retencion_id'=>0,
            'lineas_registros'=>json_encode([$line]),'lineas_registros_medios_recaudo'=>json_encode($pagos)]);
        $response=app(App\Http\Controllers\Compras\CompraController::class)->store($request);
        $this->assertEquals(302,$response->getStatusCode());
        $normal=ComprasDocEncabezado::where('descripcion','Store comparación Bot')->firstOrFail();
        $this->assertEquals($normal->valor_total,$doc->valor_total);
        $ledger=function($document){return ContabMovimiento::where($this->key($document))->select('contab_cuenta_id',DB::raw('SUM(valor_debito) debito'),DB::raw('SUM(valor_credito) credito'))->groupBy('contab_cuenta_id')->orderBy('contab_cuenta_id')->get()->toArray();};
        $this->assertEquals($ledger($normal),$ledger($doc));
        $payments=function($document){return TesoMovimiento::where($this->key($document))->select('teso_medio_recaudo_id','teso_caja_id','teso_cuenta_bancaria_id','valor_movimiento')->orderBy('teso_medio_recaudo_id')->get()->toArray();};
        $this->assertEquals($payments($normal),$payments($doc));
        $this->assertEquals(InvDocRegistro::where('inv_doc_encabezado_id',$normal->entrada_almacen_id)->sum('costo_total'),InvDocRegistro::where('inv_doc_encabezado_id',$doc->entrada_almacen_id)->sum('costo_total'));
    }

    public function test_credito_genera_cxp_y_no_tesoreria()
    {
        $doc=$this->invoice('credito');(new CompraConfirmationService())->confirm($doc);$doc=$doc->fresh();
        $this->assertEquals(0,TesoMovimiento::where($this->key($doc))->count());
        $this->assertEquals(119,App\CxP\CxpMovimiento::where($this->key($doc))->sum('saldo_pendiente'));
        $this->assertEquals(119,App\CxP\DocumentosPendientes::where($this->key($doc))->sum('saldo_pendiente'));
        $this->assertEquals(-119,ContabMovimiento::where($this->key($doc))->where('contab_cuenta_id',$this->payable->id)->sum('valor_credito'));
    }

    public function test_monto_incorrecto_no_genera_entrada_ni_movimientos()
    {
        $doc=$this->invoice();$count=InvDocEncabezado::count();
        try{(new CompraConfirmationService())->confirm($doc,$this->payments(40,78));$this->fail('Debió fallar');}
        catch(InvalidArgumentException $e){$this->assertContains('no coincide',$e->getMessage());}
        $this->assertEquals($count,InvDocEncabezado::count());$this->assertEquals('', $doc->fresh()->entrada_almacen_id);
        $this->assertEquals(0,ComprasMovimiento::where($this->key($doc))->count());
        $this->assertEquals(0,TesoMovimiento::where($this->key($doc))->count());
    }

    public function test_sin_iva_conserva_el_precio_importado_completo()
    {
        $this->provider->liquida_impuestos=0;$this->provider->save();$doc=$this->invoice();
        (new CompraConfirmationService())->confirm($doc,$this->payments());$line=$doc->lineas_registros()->first();
        $this->assertEquals(119,$line->base_impuesto);$this->assertEquals(0,$line->valor_impuesto);
        $this->assertEquals(119,InvDocRegistro::where('inv_doc_encabezado_id',$doc->fresh()->entrada_almacen_id)->sum('costo_total'));
    }

    public function test_json_pago_unico_y_mixto_no_descarta_la_ultima_fila()
    {
        $service=new CompraConfirmationPaymentService();$rows=$this->payments();
        $this->assertCount(2,$service->fromJson(json_encode($rows)));$this->assertCount(1,$service->fromJson(json_encode([$rows[0]])));
        $legacy=[['teso_medio_recaudo_id'=>$this->cashMedium->id.'-Efectivo','teso_motivo_id'=>$this->motive->id.'-Motivo','teso_caja_id'=>$this->box->id.'-Caja','teso_cuenta_bancaria_id'=>'0-','valor'=>'$119.00'],['teso_medio_recaudo_id'=>'','valor'=>'$119.00']];
        $this->assertEquals(119,$service->fromJson(json_encode($legacy))[0]['valor_recaudo']);
        foreach(['no-json','0','{}','[{"teso_medio_recaudo_id":1,"valor_recaudo":"$1,000"}]'] as $json){
            try{$service->fromJson($json);$this->fail('Debió rechazar JSON');}catch(InvalidArgumentException $e){$this->assertNotEmpty($e->getMessage());}
        }
    }

    public function test_modal_renderiza_pago_mixto_y_total_calculado_del_documento()
    {
        $doc=$this->invoice();
        $request=Illuminate\Http\Request::create('/compras/'.$doc->id,'GET',['id'=>9,'id_modelo'=>159,'id_transaccion'=>25]);
        $this->app->instance('request',$request);
        Illuminate\Support\Facades\Facade::clearResolvedInstance('request');
        View::share('errors', new Illuminate\Support\ViewErrorBag());
        $html=app(App\Http\Controllers\Compras\CompraController::class)->show($doc->id)->render();
        $this->assertContains('id="confirmar_agregar_pago"',$html);
        $this->assertContains('id="confirmar_tabla_pagos"',$html);
        $this->assertContains('data-total="119.00"',$html);
        $this->assertContains('confirmacion_documento.js',$html);
        $this->assertNotContains('<select class="form-control" id="confirmar_teso_motivo_id">',$html);
    }

    public function test_caja_ajena_o_dos_destinos_son_rechazados()
    {
        $validator=new CompraConfirmationPaymentService();$payments=$this->payments();$payments[0]['teso_cuenta_bancaria_id']=$this->bank->id;
        try{$validator->validate($payments,119,$this->company);$this->fail('Debió rechazar dos destinos');}catch(InvalidArgumentException $e){$this->assertContains('una caja o una cuenta',$e->getMessage());}
        $this->box->core_empresa_id=99999;$this->box->save();
        try{$validator->validate($this->payments(),119,$this->company);$this->fail('Debió rechazar caja ajena');}catch(InvalidArgumentException $e){$this->assertContains('no está activa',$e->getMessage());}
    }
    public function test_reutiliza_entrada_valida_sin_duplicarla_y_rechaza_entrada_ajena()
    {
        $doc=$this->invoice();$service=new BotConfirmationInspectableService();
        $entryId=$service->createEntry($doc);
        $doc->entrada_almacen_id=$entryId;$doc->save();$count=InvDocEncabezado::count();
        $service->confirm($doc,$this->payments());
        $this->assertEquals($count,InvDocEncabezado::count());
        $this->assertEquals($entryId,$doc->fresh()->entrada_almacen_id);
    }

    public function test_entrada_vinculada_ajena_falla_sin_generar_movimientos()
    {
        $doc=$this->invoice();$service=new BotConfirmationInspectableService();$entryId=$service->createEntry($doc);
        DB::table('inv_doc_encabezados')->where('id',$entryId)->update(['core_empresa_id'=>99999]);
        $doc->entrada_almacen_id=$entryId;$doc->save();
        try{$service->confirm($doc,$this->payments());$this->fail('Debió rechazar entrada ajena');}
        catch(Exception $e){$this->assertContains('pertenecer al proveedor y a la empresa',$e->getMessage());}
        $this->assertEquals(0,ComprasMovimiento::where($this->key($doc))->count());
    }

    public function test_fallo_posterior_a_contabilizar_revierte_inventario_pagos_y_asientos()
    {
        $this->product->tipo='producto';$this->product->save();$doc=$this->invoice();$count=InvDocEncabezado::count();
        try{(new BotConfirmationFailingService())->confirm($doc,$this->payments());$this->fail('Debió fallar');}
        catch(RuntimeException $e){$this->assertEquals('Fallo posterior a contabilizar',$e->getMessage());}
        $this->assertEquals($count,InvDocEncabezado::count());
        $this->assertEquals('', $doc->fresh()->entrada_almacen_id);
        foreach([ComprasMovimiento::class,TesoMovimiento::class,ContabMovimiento::class] as $model)$this->assertEquals(0,$model::where($this->key($doc))->count());
    }

    public function test_retefuente_descuenta_el_neto_y_genera_pasivo_tributario_balanceado()
    {
        config(['compras.maneja_retenciones_fuente'=>1]);
        $third=App\Core\Tercero::firstOrFail()->replicate();$third->save();config(['contabilidad.tercero_dian_id'=>$third->id]);
        $ret=App\Contabilidad\Retencion::firstOrFail()->replicate();$ret->cta_compras_id=$this->account('Retefuente prueba')->id;$ret->tasa_retencion=2.5;$ret->estado='Activo';$ret->save();
        $doc=$this->invoice('contado',2.5);$doc->lineas_registros()->update(['contab_retencion_id'=>$ret->id,'tasa_retencion'=>2.5]);
        $this->assertEquals(116.5,(new CompraConfirmationService())->getPayableTotal($doc));
        (new CompraConfirmationService())->confirm($doc,$this->payments(40,76.5));
        $this->assertEquals(119,$doc->fresh()->valor_total);
        $this->assertEquals(-116.5,TesoMovimiento::where($this->key($doc))->sum('valor_movimiento'));
        $this->assertEquals(-2.5,ContabMovimiento::where($this->key($doc))->where('contab_cuenta_id',$ret->cta_compras_id)->sum('valor_credito'));
        $this->assertEquals(0,ContabMovimiento::where($this->key($doc))->sum('valor_saldo'),'',0.01);
    }

    public function test_reteica_descuenta_del_pago_y_se_contabiliza_sin_desbalance()
    {
        config(['compras.maneja_retenciones_fuente'=>1]);
        $third=App\Core\Tercero::where('estado','Activo')->firstOrFail()->replicate();$third->save();config(['contabilidad.tercero_reteica_id'=>$third->id]);
        $category=App\Contabilidad\CategoriaRetencion::where('estado','Activo')->firstOrFail();
        config(['contabilidad.categoria_reteica_id'=>$category->id]);
        $ret=App\Contabilidad\Retencion::firstOrFail()->replicate();$ret->categoria_retenciones_id=$category->id;$ret->cta_compras_id=$this->account('ReteICA prueba')->id;$ret->tasa_retencion=0.5;$ret->estado='Activo';$ret->save();
        $doc=$this->invoice();$doc->reteica_retencion_id=$ret->id;$doc->reteica_base_manual=0;$doc->save();
        $this->assertEquals(118.5,(new CompraConfirmationService())->getPayableTotal($doc));
        (new CompraConfirmationService())->confirm($doc,$this->payments(40,78.5));
        $this->assertEquals(0.5,$doc->fresh()->reteica_valor);
        $this->assertEquals(-118.5,TesoMovimiento::where($this->key($doc))->sum('valor_movimiento'));
        $this->assertEquals(0,ContabMovimiento::where($this->key($doc))->sum('valor_saldo'),'',0.01);
    }

}


class BotConfirmationInspectableService extends CompraConfirmationService
{
    public function createEntry($doc)
    {
        $doc->load(['lineas_registros','proveedor']);$this->ensureLinesHaveMotives($doc);
        return app(App\Http\Controllers\Compras\CompraController::class)->crear_entrada_almacen($this->buildRequestFromDocument($doc));
    }
}

class BotConfirmationFailingService extends CompraConfirmationService
{
    protected function ensureAccountingIsBalanced(ComprasDocEncabezado $doc)
    {
        parent::ensureAccountingIsBalanced($doc);
        throw new RuntimeException('Fallo posterior a contabilizar');
    }
}
