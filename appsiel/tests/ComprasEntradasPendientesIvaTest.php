<?php

use App\Compras\Services\EntradasPendientesIvaService;
use App\Compras\Proveedor;
use App\Http\Controllers\Compras\CompraController;
use App\Http\Controllers\Compras\FacturaEntradaPendienteController;
use App\Inventarios\Services\AverageCost;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasEntradasPendientesIvaTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config(['database.connections.pendientes_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>''],'configuracion.liquidacion_impuestos'=>1]);
        DB::setDefaultConnection('pendientes_test');
        Schema::create('inv_productos',function($t){$t->increments('id');$t->integer('impuesto_id');$t->string('tipo')->default('producto');});
        Schema::create('contab_impuestos',function($t){$t->increments('id');$t->double('tasa_impuesto');});
        Schema::create('compras_proveedores',function($t){$t->increments('id');$t->integer('core_tercero_id');$t->integer('liquida_impuestos');});
        Schema::create('inv_doc_encabezados',function($t){
            $t->increments('id');$t->integer('core_empresa_id');$t->integer('core_tercero_id');$t->integer('core_tipo_transaccion_id')->default(35);
            $t->integer('core_tipo_doc_app_id')->default(2);$t->integer('consecutivo');$t->string('estado')->default('Pendiente');
            $t->string('fecha')->default('2026-11-12');$t->boolean('costos_incluyen_iva')->nullable();$t->timestamps();
        });
        Schema::create('inv_doc_registros',function($t){
            $t->increments('id');$t->integer('inv_doc_encabezado_id');$t->integer('inv_producto_id');$t->integer('inv_bodega_id')->default(15);
            $t->double('cantidad');$t->double('costo_unitario');$t->double('costo_total');$t->timestamps();
        });
        Schema::create('inv_movimientos',function($t){
            $t->increments('id');$t->integer('core_empresa_id');$t->integer('inv_doc_encabezado_id');$t->integer('inv_producto_id');
            $t->double('cantidad');$t->double('costo_unitario');$t->double('costo_total');$t->timestamps();
        });
        Schema::create('contab_movimientos',function($t){
            $t->increments('id');$t->integer('core_empresa_id');$t->integer('core_tipo_transaccion_id')->default(35);$t->integer('core_tipo_doc_app_id')->default(2);
            $t->integer('consecutivo');$t->integer('inv_producto_id');foreach(['valor_operacion','valor_debito','valor_credito','valor_saldo'] as $c)$t->double($c);$t->timestamps();
        });
        DB::table('contab_impuestos')->insert([['id'=>1,'tasa_impuesto'=>19],['id'=>2,'tasa_impuesto'=>0],['id'=>3,'tasa_impuesto'=>5]]);
        DB::table('inv_productos')->insert([['id'=>200,'impuesto_id'=>1],['id'=>201,'impuesto_id'=>2],['id'=>202,'impuesto_id'=>3]]);
        DB::table('compras_proveedores')->insert(['id'=>17,'core_tercero_id'=>89,'liquida_impuestos'=>1]);
        foreach([10,11] as $id)DB::table('inv_doc_encabezados')->insert(['id'=>$id,'core_empresa_id'=>1,'core_tercero_id'=>89,'consecutivo'=>$id]);
        foreach([[10,200,1,8900],[10,200,1,11900],[11,202,2,10500],[11,201,1,5000]] as $r){
            $line=['inv_doc_encabezado_id'=>$r[0],'inv_producto_id'=>$r[1],'cantidad'=>$r[2],'costo_unitario'=>$r[3],'costo_total'=>$r[2]*$r[3]];
            DB::table('inv_doc_registros')->insert($line);DB::table('inv_movimientos')->insert($line+['core_empresa_id'=>1]);
            $total=$r[2]*$r[3];$doc=['core_empresa_id'=>1,'consecutivo'=>$r[0],'inv_producto_id'=>$r[1],'valor_operacion'=>$total];
            DB::table('contab_movimientos')->insert($doc+['valor_debito'=>$total,'valor_credito'=>0,'valor_saldo'=>$total]);
            DB::table('contab_movimientos')->insert($doc+['valor_debito'=>0,'valor_credito'=>-$total,'valor_saldo'=>-$total]);
        }
    }

    protected function referencias(){return [(object)['id_doc'=>10],(object)['id_doc'=>11]];}

    protected function servicio($esperadas=2)
    {
        $costos=Mockery::mock(AverageCost::class);
        $costos->shouldReceive('calculate_average_cost')->times($esperadas)->andReturnUsing(function($b,$p,$c){return $c;});
        $costos->shouldReceive('set_costo_promedio')->times($esperadas);
        return new EntradasPendientesIvaService($costos);
    }

    public function test_varias_entradas_con_iva_incluido_conservan_total_y_normalizan_costos_una_vez()
    {
        // Misma numeración y producto de otra empresa deben permanecer intactos.
        DB::table('contab_movimientos')->insert(['core_empresa_id'=>2,'consecutivo'=>10,'inv_producto_id'=>200,'valor_operacion'=>8900,'valor_debito'=>8900,'valor_credito'=>0,'valor_saldo'=>8900]);
        $proveedor=Proveedor::find(17);$service=$this->servicio();
        DB::transaction(function()use($service,$proveedor){$service->preparar($proveedor,$this->referencias(),1);});
        $filas=DB::table('inv_doc_registros')->orderBy('id')->get();
        $this->assertEquals(8900/1.19,$filas[0]->costo_total,'',0.00001);
        $this->assertEquals(10000,$filas[1]->costo_total,'',0.00001);
        $this->assertEquals(20000,$filas[2]->costo_total,'',0.00001);
        $this->assertEquals(5000,$filas[3]->costo_total);
        $factura=(object)['proveedor_id'=>17,'proveedor'=>$proveedor];
        $this->assertEquals(46800,FacturaEntradaPendienteController::get_total_documento_desde_lineas_registros_desde_entrada($factura,$this->referencias()),'',0.00001);
        $this->assertEquals(0,DB::table('inv_doc_encabezados')->where('costos_incluyen_iva',1)->count());
        $saldo=DB::table('contab_movimientos')->where('core_empresa_id',1)->sum('valor_saldo');$this->assertEquals(0,$saldo,'',0.00001);
        $this->assertEquals(8900,DB::table('contab_movimientos')->where('core_empresa_id',2)->value('valor_debito'));
        $antes=DB::table('inv_doc_registros')->orderBy('id')->get();
        // Simula una entrada devuelta a Pendiente al anular una factura.
        $service->preparar($proveedor,$this->referencias(),1);
        $this->assertEquals($antes,DB::table('inv_doc_registros')->orderBy('id')->get());
        Mockery::close();
    }

    public function test_proveedor_sin_iva_conserva_todos_los_costos_y_el_pago()
    {
        DB::table('compras_proveedores')->where('id',17)->update(['liquida_impuestos'=>0]);
        $proveedor=Proveedor::find(17);$antes=DB::table('inv_doc_registros')->get();
        $this->servicio(0)->preparar($proveedor,$this->referencias(),1);
        $this->assertEquals($antes,DB::table('inv_doc_registros')->get());
        $this->assertEquals(46800,FacturaEntradaPendienteController::get_total_documento_desde_lineas_registros_desde_entrada((object)['proveedor_id'=>17,'proveedor'=>$proveedor],$this->referencias()));
        Mockery::close();
    }

    public function test_fallo_posterior_revierte_costos_contabilidad_e_indicador()
    {
        $antes=DB::table('inv_doc_registros')->get();$contab=DB::table('contab_movimientos')->get();$service=$this->servicio();
        try{DB::transaction(function()use($service){$service->preparar(Proveedor::find(17),$this->referencias(),1);throw new RuntimeException('Fallo al guardar factura');});$this->fail('Debió revertirse');}
        catch(RuntimeException $e){$this->assertEquals('Fallo al guardar factura',$e->getMessage());}
        $this->assertEquals($antes,DB::table('inv_doc_registros')->get());$this->assertEquals($contab,DB::table('contab_movimientos')->get());
        $this->assertEquals(2,DB::table('inv_doc_encabezados')->whereNull('costos_incluyen_iva')->count());Mockery::close();
    }

    public function test_rechaza_repetidas_y_entradas_de_otra_empresa_proveedor_o_ya_facturadas()
    {
        $service=$this->servicio(0);$proveedor=Proveedor::find(17);
        foreach(['duplicada','empresa','proveedor','estado'] as $caso){
            DB::table('inv_doc_encabezados')->where('id',11)->update(['core_empresa_id'=>1,'core_tercero_id'=>89,'estado'=>'Pendiente']);
            $refs=$this->referencias();
            if($caso==='duplicada')$refs[]=(object)['id_doc'=>10];
            if($caso==='empresa')DB::table('inv_doc_encabezados')->where('id',11)->update(['core_empresa_id'=>2]);
            if($caso==='proveedor')DB::table('inv_doc_encabezados')->where('id',11)->update(['core_tercero_id'=>90]);
            if($caso==='estado')DB::table('inv_doc_encabezados')->where('id',11)->update(['estado'=>'Facturada']);
            try{$service->preparar($proveedor,$refs,1);$this->fail('Debió rechazar '.$caso);}catch(InvalidArgumentException $e){$this->assertNotEmpty($e->getMessage());}
        }
        $this->assertEquals(46800,DB::table('inv_doc_registros')->sum('costo_total'));Mockery::close();
    }
    public function test_vista_pendientes_conserva_precio_final_antes_y_despues_de_convertir()
    {
        \Illuminate\Support\Facades\Auth::setUser(new App\User(['empresa_id'=>1]));
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT',function(){return implode('',func_get_args());});
        DB::connection()->getPdo()->sqliteCreateFunction('DATE_FORMAT',function($value,$format){return date('d-m-Y',strtotime($value));});
        Schema::table('inv_doc_encabezados',function($t){
            foreach(['descripcion','hora_inicio','hora_finalizacion','creado_por','modificado_por'] as $c)$t->string($c)->default('');
            $t->integer('inv_bodega_id')->default(15);$t->integer('vtas_doc_encabezado_origen_id')->default(0);
        });
        Schema::create('core_tipos_docs_apps',function($t){$t->increments('id');$t->string('descripcion');$t->string('prefijo');});
        Schema::create('core_terceros',function($t){$t->increments('id');foreach(['descripcion','razon_social','numero_identificacion','direccion1','telefono1'] as $c)$t->string($c)->nullable();});
        Schema::create('vtas_clientes',function($t){$t->increments('id');$t->integer('core_tercero_id');});
        $opciones=['precios_incluyen_iva'=>true,'liquida_impuestos'=>1];
        $antes=App\Inventarios\InvDocEncabezado::get_documentos_por_transaccion(35,89,'Pendiente',$opciones);
        $this->assertEquals(46800,$antes->sum('total_documento_mas_iva'),'',0.00001);
        $this->assertEquals(20800/1.19,$antes[0]->total_documento,'',0.00001);
        $this->servicio()->preparar(Proveedor::find(17),$this->referencias(),1);
        $despues=App\Inventarios\InvDocEncabezado::get_documentos_por_transaccion(35,89,'Pendiente',$opciones);
        $this->assertEquals($antes->sum('total_documento_mas_iva'),$despues->sum('total_documento_mas_iva'),'',0.00001);
        Mockery::close();
    }

    public function test_empresa_sin_iva_no_extrae_impuestos_aunque_proveedor_si_liquide()
    {
        config(['configuracion.liquidacion_impuestos'=>0]);
        $antes=DB::table('inv_doc_registros')->get();$this->servicio(0)->preparar(Proveedor::find(17),$this->referencias(),1);
        $this->assertEquals($antes,DB::table('inv_doc_registros')->get());Mockery::close();
    }

}
