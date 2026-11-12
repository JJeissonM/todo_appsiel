<?php

use App\Http\Controllers\Compras\CompraController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ComprasPrecioSinIvaTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        config(['database.connections.precio_test'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('precio_test');
        config(['configuracion.liquidacion_impuestos'=>1]);
        Schema::create('inv_productos', function ($t) { $t->increments('id'); $t->integer('impuesto_id'); });
        Schema::create('contab_impuestos', function ($t) { $t->increments('id'); $t->double('tasa_impuesto'); });
        Schema::create('compras_proveedores', function ($t) { $t->increments('id'); $t->integer('liquida_impuestos'); });
        DB::table('inv_productos')->insert(['id'=>200,'impuesto_id'=>1]);
        DB::table('contab_impuestos')->insert(['id'=>1,'tasa_impuesto'=>19]);
        DB::table('compras_proveedores')->insert(['id'=>17,'liquida_impuestos'=>0]);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_entrada_sin_iva_conserva_8900_y_con_iva_conserva_su_base()
    {
        $inventario = Mockery::mock('alias:App\Http\Controllers\Inventarios\InventarioController');
        $capturadas = [];
        $inventario->shouldReceive('crear_documento')->twice()->andReturnUsing(function ($request, $lineas, $modelo) use (&$capturadas) {
            $capturadas[] = clone $lineas[0];
            return 99;
        });
        config(['compras.ea_tipo_doc_app_id'=>27,'compras.ea_tipo_transaccion_id'=>35,'compras.ea_modelo_id'=>165]);
        Schema::create('core_tipos_docs_apps', function ($t) { $t->increments('id'); $t->string('estado'); });
        DB::table('core_tipos_docs_apps')->insert(['id'=>27,'estado'=>'Activo']);
        $controller = new CompraController();
        foreach ([0,1] as $liquida) {
            $base = $liquida ? 8900 / 1.19 : 8900;
            $linea = (object)['inv_producto_id'=>200,'cantidad'=>1,'precio_unitario'=>8900,'precio_total'=>8900,'costo_unitario'=>$base,'costo_total'=>$base,'tasa_impuesto'=>$liquida ? 19 : 0];
            $request = new Request(['lineas_registros'=>json_encode([$linea]),'liquida_impuestos'=>$liquida,'inv_bodega_id'=>15,'core_tipo_transaccion_id'=>25,'core_tipo_doc_app_id'=>17]);
            $this->assertEquals(99,$controller->crear_entrada_almacen($request));
            $this->assertEquals(25,$request->core_tipo_transaccion_id);
            $this->assertEquals(17,$request->core_tipo_doc_app_id);
            DB::table('compras_proveedores')->where('id',17)->update(['liquida_impuestos'=>$liquida]);
            $datos = CompraController::get_datos_factura_desde_registro_entrada(end($capturadas),17);
            $this->assertEquals(8900,$datos['precio_total'],'El total reconstruido coincide con el pago',0.000001);
            $this->assertEquals($liquida ? 19 : 0,$datos['tasa_impuesto']);
        }
        $this->assertEquals(8900,$capturadas[0]->costo_unitario);
        $this->assertEquals(8900 / 1.19,$capturadas[1]->costo_unitario,'',0.000001);
        Mockery::close();
    }

    public function test_cantidad_y_descuento_no_se_aplican_dos_veces()
    {
        $linea = (object)['inv_producto_id'=>200,'cantidad'=>3,'precio_unitario'=>8900,'precio_total'=>24030,'costo_unitario'=>8010,'costo_total'=>24030,'tasa_descuento'=>10,'valor_total_descuento'=>2670];
        $controller = new CompraController();
        $normalizada = $controller->recalcular_lineas_registros([$linea])[0];
        $datos = CompraController::get_datos_factura_desde_registro_entrada($normalizada,17);
        $this->assertEquals(8010,$normalizada->costo_unitario);
        $this->assertEquals(24030,$datos['precio_total']);
        $this->assertEquals(24030,$controller->get_total_documento_desde_lineas_registros([$normalizada]));
        $this->assertEquals(2670,$normalizada->valor_total_descuento);
    }

    public function test_cantidad_cero_no_divide_por_cero()
    {
        $linea = (object)['inv_producto_id'=>200,'cantidad'=>0,'precio_total'=>0,'costo_unitario'=>8900,'costo_total'=>0];
        $normalizada = (new CompraController())->recalcular_lineas_registros([$linea])[0];
        $this->assertEquals(0,$normalizada->costo_unitario);
        $this->assertEquals(0,$normalizada->costo_total);
    }
    protected function prepararEntradasPendientes()
    {
        Schema::create('inv_doc_registros', function ($t) {
            $t->increments('id'); $t->integer('inv_doc_encabezado_id'); $t->integer('inv_producto_id');
            $t->double('cantidad'); $t->double('costo_unitario'); $t->double('costo_total');
        });
        DB::table('inv_doc_registros')->insert([
            ['inv_doc_encabezado_id'=>10,'inv_producto_id'=>200,'cantidad'=>1,'costo_unitario'=>8900,'costo_total'=>8900],
            ['inv_doc_encabezado_id'=>11,'inv_producto_id'=>200,'cantidad'=>2,'costo_unitario'=>8900,'costo_total'=>17800],
        ]);
        return [(object)['id_doc'=>10], (object)['id_doc'=>11]];
    }

    public function test_varias_entradas_con_precio_final_no_agregan_iva_a_proveedor_sin_iva()
    {
        $entradas = $this->prepararEntradasPendientes();
        $factura = (object)['proveedor_id'=>17,'proveedor'=>App\Compras\Proveedor::find(17)];
        $total = App\Http\Controllers\Compras\FacturaEntradaPendienteController::get_total_documento_desde_lineas_registros_desde_entrada($factura,$entradas);
        $this->assertEquals(26700,$total);
        $total_lineas = 0;
        foreach (App\Inventarios\InvDocRegistro::all() as $registro) {
            $datos = CompraController::get_datos_factura_desde_registro_entrada($registro,17);
            $this->assertEquals(0,$datos['tasa_impuesto']);
            $this->assertEquals(0,$datos['valor_impuesto']);
            $this->assertEquals(8900,$datos['precio_unitario']);
            $total_lineas += $datos['precio_total'];
        }
        $this->assertEquals($total,$total_lineas);
    }

    public function test_varias_entradas_con_iva_desactivado_en_empresa_conservan_importes()
    {
        $entradas = $this->prepararEntradasPendientes();
        DB::table('compras_proveedores')->where('id',17)->update(['liquida_impuestos'=>1]);
        config(['configuracion.liquidacion_impuestos'=>0]);
        $factura = (object)['proveedor_id'=>17,'proveedor'=>App\Compras\Proveedor::find(17)];
        $this->assertEquals(26700,App\Http\Controllers\Compras\FacturaEntradaPendienteController::get_total_documento_desde_lineas_registros_desde_entrada($factura,$entradas));
    }

    public function test_varias_entradas_con_costos_base_e_iva_se_liquidan_una_sola_vez()
    {
        $entradas = $this->prepararEntradasPendientes();
        DB::table('compras_proveedores')->where('id',17)->update(['liquida_impuestos'=>1]);
        // Para proveedor con IVA, los costos de inventario de esta prueba son bases sin IVA.
        DB::table('inv_doc_registros')->where('inv_doc_encabezado_id',10)->update(['costo_unitario'=>8900/1.19,'costo_total'=>8900/1.19]);
        DB::table('inv_doc_registros')->where('inv_doc_encabezado_id',11)->update(['costo_unitario'=>8900/1.19,'costo_total'=>17800/1.19]);
        $factura = (object)['proveedor_id'=>17,'proveedor'=>App\Compras\Proveedor::find(17)];
        $this->assertEquals(26700,App\Http\Controllers\Compras\FacturaEntradaPendienteController::get_total_documento_desde_lineas_registros_desde_entrada($factura,$entradas),'',0.00001);
        $total_lineas = 0;
        foreach (App\Inventarios\InvDocRegistro::all() as $registro) {
            $datos = CompraController::get_datos_factura_desde_registro_entrada($registro,17);
            $this->assertEquals(19,$datos['tasa_impuesto']);
            $this->assertEquals(8900,$datos['precio_unitario'],'',0.00001);
            $total_lineas += $datos['precio_total'];
        }
        $this->assertEquals(26700,$total_lineas,'',0.00001);
    }

}
