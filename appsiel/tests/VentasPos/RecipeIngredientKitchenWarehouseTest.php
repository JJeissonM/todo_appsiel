<?php

use App\Inventarios\Services\InvDocumentsLinesService;
use App\VentasPos\Services\RecipeServices;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RecipeIngredientKitchenWarehouseTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['database.connections.recipes_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''
        ]]);
        DB::setDefaultConnection('recipes_test');
        Auth::shouldReceive('user')->andReturn((object)['empresa_id' => 1]);
        config(['inventarios.maneja_costo_promedio_por_bodegas' => 1]);

        foreach ([
            'CREATE TABLE inv_productos (id INTEGER, inv_grupo_id INTEGER, descripcion TEXT, unidad_medida1 TEXT, precio_compra REAL)',
            'CREATE TABLE inv_recetas_cocina (id INTEGER, item_platillo_id INTEGER, item_ingrediente_id INTEGER, cantidad_porcion REAL)',
            'CREATE TABLE vtas_restaurante_cocinas (id INTEGER, grupo_inventarios_id INTEGER, bodega_default_id INTEGER, estado TEXT)',
            'CREATE TABLE inv_movimientos (core_empresa_id INTEGER, inv_producto_id INTEGER, inv_bodega_id INTEGER, fecha TEXT, cantidad REAL)',
            'CREATE TABLE inv_motivos (id INTEGER, descripcion TEXT)',
            'CREATE TABLE inv_costo_prom_productos (inv_producto_id INTEGER, inv_bodega_id INTEGER, costo_promedio REAL)',
            "INSERT INTO inv_productos VALUES (383,4,'MENU INFANTIL','UND',4000),(384,5,'JUGO INFANTIL','UND',1050),(50,6,'INSUMO JUGO','UND',100)",
            'INSERT INTO inv_recetas_cocina VALUES (1,383,384,1)',
            "INSERT INTO vtas_restaurante_cocinas VALUES (3,5,1,'Activo')",
            "INSERT INTO inv_motivos VALUES (3,'Consumo'),(4,'Producto final')",
            // La bodega del platillo tiene stock abundante: no debe ganar prioridad.
            "INSERT INTO inv_movimientos VALUES (1,384,20,'2026-10-07',100)",
            'INSERT INTO inv_costo_prom_productos VALUES (384,20,900),(384,1,1050)'
        ] as $sql) {
            DB::statement($sql);
        }
        require_once __DIR__.'/../../database/migrations/2026_10_07_000001_add_bodega_default_id_to_inv_productos.php';
        (new AddBodegaDefaultIdToInvProductos())->up();
    }

    protected function tearDown()
    {
        DB::disconnect('recipes_test');
        Mockery::close();
        parent::tearDown();
    }

    protected function generarLineas($service)
    {
        $json = $service->get_lineas_registros_ensamble(
            $service->get_obj_cantidad_facturada_item(383, 2),
            20, ['motivo_salida_id' => 3, 'motivo_entrada_id' => 4], '2026-10-07', 51107
        );
        return (new InvDocumentsLinesService())->preparar_array_lineas_registros(20, $json, null);
    }

    /** @dataProvider saldosPlatillo */
    public function test_saldo_negativo_no_aumenta_ensamble_de_la_factura($saldo, $cantidadEsperada)
    {
        DB::table('inv_movimientos')->insert([
            'core_empresa_id'=>1, 'inv_producto_id'=>383, 'inv_bodega_id'=>20,
            'fecha'=>'2026-10-07', 'cantidad'=>$saldo
        ]);
        $service = new RecipeServices();
        $json = $service->get_lineas_registros_ensamble(
            $service->get_obj_cantidad_facturada_item(383, 1), 20,
            ['motivo_salida_id'=>3,'motivo_entrada_id'=>4], '2026-10-07'
        );
        if ($cantidadEsperada == 0) {
            $this->assertSame(99, $json);
            return;
        }
        $lines = (new InvDocumentsLinesService())->preparar_array_lineas_registros(20, $json, null);
        $this->assertEquals($cantidadEsperada, $lines[count($lines)-1]->cantidad);
        $this->assertEquals($cantidadEsperada, collect($lines)->where('inv_producto_id', '384')->sum('cantidad'));
    }

    public function saldosPlatillo()
    {
        return ['caso POS 21077'=>[-10,1], 'sin stock'=>[0,1], 'stock parcial'=>[0.5,0.5], 'stock suficiente'=>[1,0]];
    }

    public function test_saldo_negativo_del_ingrediente_no_aumenta_el_ensamble_anidado()
    {
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,1,'2026-10-07',-10)");
        $service = new RecipeKitchenNestedDocumentSpy();
        $this->generarLineas($service);
        $this->assertCount(1, $service->documents);
        $this->assertEquals(2, $service->documents[0]['lines'][1]->cantidad);
        $this->assertEquals(6, $service->documents[0]['lines'][0]->cantidad);
    }

    public function test_cocina_del_platillo_tiene_prioridad_sobre_bodega_y_cocina_del_ingrediente()
    {
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo')");
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        $lines = $this->generarLineas(new RecipeServices());
        $this->assertEquals(20, $lines[0]->inv_bodega_id);
        $this->assertEquals(900, $lines[0]->costo_unitario);
    }

    public function test_sin_stock_en_cocina_del_platillo_consume_de_la_siguiente_bodega_sin_ensamblar()
    {
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo')");
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        DB::statement('DELETE FROM inv_movimientos WHERE inv_producto_id=384');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,30,'2026-10-07',100)");
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(0, $service->documents);
        $this->assertEquals(30, $lines[0]->inv_bodega_id);
    }

    public function test_ingrediente_compartido_conserva_la_bodega_de_cada_platillo()
    {
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo'),(4,7,40,'Activo')");
        DB::statement("INSERT INTO inv_productos (id,inv_grupo_id,descripcion,unidad_medida1,precio_compra) VALUES (400,7,'OTRO PLATILLO','UND',4000)");
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (3,400,384,1)');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,40,'2026-10-07',100)");
        $service = new RecipeServices();
        $quantities = $service->get_obj_cantidad_facturada_item(383, 1)->merge($service->get_obj_cantidad_facturada_item(400, 1));
        $json = $service->get_lineas_registros_ensamble($quantities, 20, ['motivo_salida_id' => 3, 'motivo_entrada_id' => 4], '2026-10-07');
        $lines = (new InvDocumentsLinesService())->preparar_array_lineas_registros(20, $json, null);
        $this->assertEquals(384, $lines[0]->inv_producto_id);
        $this->assertEquals(20, $lines[0]->inv_bodega_id);
        $this->assertEquals(384, $lines[2]->inv_producto_id);
        $this->assertEquals(40, $lines[2]->inv_bodega_id);
    }

    public function test_bodega_por_defecto_del_producto_prevalece_sobre_cocina_y_existencias()
    {
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        DB::statement('INSERT INTO inv_costo_prom_productos VALUES (384,30,1200)');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,30,'2026-10-07',2)");
        $lines = $this->generarLineas(new RecipeServices());
        $this->assertEquals(30, $lines[0]->inv_bodega_id);
        $this->assertEquals(1200, $lines[0]->costo_unitario);
        $this->assertEquals(20, $lines[1]->inv_bodega_id);
    }

    public function test_si_bodega_default_no_tiene_stock_consume_de_la_cocina_del_ingrediente()
    {
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        // Stock en la cocina no sustituye el faltante en la bodega elegida por el producto.
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,1,'2026-10-07',100)");
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(0, $service->documents);
        $this->assertEquals(1, $lines[0]->inv_bodega_id);
    }

    /** @dataProvider stocksPorPrioridad */
    public function test_consume_stock_parcial_en_orden_y_solo_ensambla_faltante($stockFinal, $faltante)
    {
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo')");
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        DB::statement('DELETE FROM inv_movimientos');
        DB::table('inv_movimientos')->insert([
            ['core_empresa_id'=>1,'inv_producto_id'=>384,'inv_bodega_id'=>20,'fecha'=>'2026-10-07','cantidad'=>0.5],
            ['core_empresa_id'=>1,'inv_producto_id'=>384,'inv_bodega_id'=>30,'fecha'=>'2026-10-07','cantidad'=>0.5],
            ['core_empresa_id'=>1,'inv_producto_id'=>384,'inv_bodega_id'=>1,'fecha'=>'2026-10-07','cantidad'=>$stockFinal]
        ]);
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        DB::statement('INSERT INTO inv_costo_prom_productos VALUES (384,30,1200)');
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(4, $lines);
        $this->assertEquals(20, $lines[0]->inv_bodega_id);
        $this->assertEquals(0.5, $lines[0]->cantidad);
        $this->assertEquals(30, $lines[1]->inv_bodega_id);
        $this->assertEquals(0.5, $lines[1]->cantidad);
        $this->assertEquals(1, $lines[2]->inv_bodega_id);
        $this->assertEquals($stockFinal + $faltante, $lines[2]->cantidad);
        $this->assertEquals(2, $lines[3]->cantidad);
        $this->assertEquals($lines[0]->costo_total + $lines[1]->costo_total + $lines[2]->costo_total, $lines[3]->costo_total);
        $this->assertCount($faltante > 0 ? 1 : 0, $service->documents);
        if ($faltante > 0) {
            $nested = $service->documents[0];
            $this->assertEquals(1, $nested['warehouse']);
            $this->assertEquals($faltante, $nested['lines'][1]->cantidad);
            $this->assertEquals(1, $nested['lines'][1]->inv_bodega_id);
        }
    }

    /** @dataProvider prioridadesEnsamble */
    public function test_prioridad_de_ensamble_independiente_del_orden_de_consumo($cocinaIngrediente, $defaultIngrediente, $cocinaPlatillo, $esperada)
    {
        DB::statement('DELETE FROM inv_movimientos');
        if (!$cocinaIngrediente) {
            DB::statement('DELETE FROM vtas_restaurante_cocinas WHERE grupo_inventarios_id=5');
        }
        if ($cocinaPlatillo) {
            DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo')");
        }
        DB::table('inv_productos')->where('id', 384)->update(['bodega_default_id' => $defaultIngrediente]);
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(1, $service->documents);
        $this->assertEquals($esperada, $service->documents[0]['warehouse']);
        $this->assertEquals($esperada, $service->documents[0]['lines'][1]->inv_bodega_id);
        $this->assertEquals(2, $service->documents[0]['lines'][1]->cantidad);
        $this->assertEquals($esperada, $lines[0]->inv_bodega_id);
        $this->assertEquals(2, $lines[0]->cantidad);
    }

    public function prioridadesEnsamble()
    {
        return [
            'cocina ingrediente' => [true, 30, true, 1],
            'default ingrediente' => [false, 30, true, 30],
            'cocina platillo' => [false, null, true, 20],
            'documento' => [false, null, false, 20]
        ];
    }

    public function stocksPorPrioridad()
    {
        return [[1, 0], [0.25, 0.75]];
    }

    public function test_no_reserva_dos_veces_stock_compartido_por_platillos()
    {
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo'),(4,7,40,'Activo')");
        DB::statement("INSERT INTO inv_productos (id,inv_grupo_id,descripcion,unidad_medida1,precio_compra) VALUES (400,7,'OTRO','UND',4000)");
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (3,400,384,1)');
        DB::statement('UPDATE inv_productos SET bodega_default_id=30 WHERE id=384');
        DB::statement('DELETE FROM inv_movimientos');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,30,'2026-10-07',1),(1,384,1,'2026-10-07',1)");
        $service = new RecipeServices();
        $quantities = $service->get_obj_cantidad_facturada_item(383, 1)->merge($service->get_obj_cantidad_facturada_item(400, 1));
        $json = $service->get_lineas_registros_ensamble($quantities, 20, ['motivo_salida_id'=>3,'motivo_entrada_id'=>4], '2026-10-07');
        $lines = (new InvDocumentsLinesService())->preparar_array_lineas_registros(20, $json, null);
        $this->assertEquals(30, $lines[0]->inv_bodega_id);
        $this->assertEquals(1, $lines[0]->cantidad);
        $this->assertEquals(1, $lines[2]->inv_bodega_id);
        $this->assertEquals(1, $lines[2]->cantidad);
    }

    public function test_bodega_opcional_se_guarda_y_se_puede_limpiar()
    {
        $item = \App\Inventarios\InvProducto::find(384);
        $this->assertNull($item->bodega_default_id);
        $item->fill(['bodega_default_id' => '30']);
        $this->assertSame(30, $item->bodega_default_id);
        $item->fill(['bodega_default_id' => '']);
        $this->assertNull($item->bodega_default_id);
        $item->fill(['bodega_default_id' => null]);
        $this->assertNull($item->bodega_default_id);
    }

    public function test_migracion_registra_selector_opcional_del_catalogo_sin_duplicados()
    {
        DB::statement('CREATE TABLE sys_modelos (id INTEGER, name_space TEXT)');
        DB::table('sys_modelos')->insert(['id' => 21, 'name_space' => 'App\\Inventarios\\InvProducto']);
        DB::statement('CREATE TABLE sys_campos (id INTEGER PRIMARY KEY AUTOINCREMENT, descripcion TEXT, tipo TEXT, name TEXT, opciones TEXT, value TEXT, atributos TEXT, definicion TEXT, requerido INTEGER, editable INTEGER, unico INTEGER, created_at TEXT, updated_at TEXT)');
        DB::statement('CREATE TABLE sys_modelo_tiene_campos (core_modelo_id INTEGER, core_campo_id INTEGER, orden INTEGER)');
        $migration = new AddBodegaDefaultIdToInvProductos();
        $migration->up();
        $migration->up();
        $field = DB::table('sys_campos')->where('name', 'bodega_default_id')->first();
        $this->assertNotNull($field);
        $this->assertEquals(0, $field->requerido);
        $this->assertSame('model_App\\Inventarios\\InvBodega', $field->opciones);
        $this->assertEquals(1, DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', 21)->count());
    }

    public function test_guardado_preserva_la_bodega_del_ingrediente_en_documento_y_kardex()
    {
        // Esquema de persistencia aislado; se ejecuta el guardado real de las líneas.
        DB::statement('CREATE TABLE inv_doc_registros (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        foreach ([new \App\Inventarios\InvDocRegistro(), new \App\Inventarios\InvMovimiento()] as $model) {
            $table = $model->getTable();
            $existing = DB::getSchemaBuilder()->getColumnListing($table);
            foreach (array_merge($model->getFillable(), ['created_at', 'updated_at']) as $column) {
                if ($column !== 'turno_operativo_id' && !in_array($column, $existing)) {
                    DB::statement("ALTER TABLE $table ADD COLUMN $column TEXT");
                }
            }
        }
        foreach (['inv_productos', 'inv_costo_prom_productos'] as $table) {
            DB::statement("ALTER TABLE $table ADD COLUMN created_at TEXT");
            DB::statement("ALTER TABLE $table ADD COLUMN updated_at TEXT");
        }
        DB::statement('ALTER TABLE inv_costo_prom_productos ADD COLUMN id INTEGER');
        DB::statement('ALTER TABLE inv_productos ADD COLUMN tipo TEXT');
        DB::statement("UPDATE inv_productos SET tipo='producto'");
        DB::statement('ALTER TABLE inv_motivos ADD COLUMN movimiento TEXT');
        DB::statement("UPDATE inv_motivos SET movimiento=CASE WHEN id=3 THEN 'salida' ELSE 'entrada' END");

        $lines = $this->generarLineas(new RecipeServices());
        $header = (object)['id' => 100, 'consecutivo' => 1, 'hora_inicio' => null, 'hora_finalizacion' => null];
        $data = [
            'core_empresa_id' => 1, 'inv_bodega_id' => 20, 'fecha' => '2026-10-07',
            'core_tipo_transaccion_id' => 4, 'core_tipo_doc_app_id' => 1,
            'estado' => 'Activo', 'creado_por' => 'test@example.com'
        ];
        (new \App\Inventarios\Services\InvDocumentsService())->store_document_lines($data, $header, $lines);

        foreach (['inv_doc_registros', 'inv_movimientos'] as $table) {
            $juice = DB::table($table)->where('inv_doc_encabezado_id', 100)->where('inv_producto_id', 384)->first();
            $dish = DB::table($table)->where('inv_doc_encabezado_id', 100)->where('inv_producto_id', 383)->first();
            $this->assertEquals(1, $juice->inv_bodega_id, $table);
            $this->assertEquals(-2, $juice->cantidad, $table);
            $this->assertEquals(-2100, $juice->costo_total, $table);
            $this->assertEquals(20, $dish->inv_bodega_id, $table);
            $this->assertEquals(2, $dish->cantidad, $table);
        }
        // La remisión descuenta el platillo en su cocina aunque la factura
        // conserve la bodega predeterminada del PDV. La siguiente venta debe ensamblar.
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo')");
        $invoiceLine = new \App\VentasPos\DocRegistro();
        $invoiceLine->forceFill(['id' => 10, 'inv_producto_id' => 383, 'inv_bodega_id' => 1, 'cantidad' => 2, 'vtas_motivo_id' => 17]);
        $inventory = new \App\VentasPos\Services\InventoriesServices();
        $warehouse = $inventory->get_bodega_id_linea($invoiceLine, 1, true);
        $this->assertEquals(20, $warehouse);
        $this->assertEquals(1, $inventory->get_bodega_id_linea($invoiceLine, 1, false));
        $grouped = $inventory->agregar_bodega_a_cantidades_facturadas(collect([$invoiceLine]), 1);
        $this->assertEquals(20, $grouped->first()->inv_bodega_id);
        $deliveryHeader = new \App\Inventarios\InvDocEncabezado();
        $deliveryHeader->forceFill(array_replace($data, ['id' => 101, 'consecutivo' => 2, 'core_tipo_transaccion_id' => 24]));
        (new \App\Inventarios\Services\InvDocumentsService())->store_delivery_note_lines([
            'inv_bodega_id' => 1, 'invoice_doc_lines' => collect([$invoiceLine]),
            'invoice_doc_line_bodega_ids' => [10 => $warehouse]
        ], $deliveryHeader);
        $this->assertEquals(0, \App\Inventarios\InvMovimiento::get_cantidad_existencia_item(383, 20, '2026-10-07'));
        $this->assertCount(2, $this->generarLineas(new RecipeServices()));

        // La contabilización también conserva la bodega de cada producto.
        DB::statement('CREATE TABLE inv_grupos (id INTEGER, cta_inventarios_id INTEGER)');
        DB::statement('INSERT INTO inv_grupos VALUES (4,1405),(5,1405)');
        DB::statement('ALTER TABLE inv_motivos ADD COLUMN cta_contrapartida_id INTEGER');
        DB::statement('UPDATE inv_motivos SET cta_contrapartida_id=6135');
        $accounting = new \App\Contabilidad\ContabMovimiento();
        DB::statement('CREATE TABLE contab_movimientos (id INTEGER PRIMARY KEY AUTOINCREMENT)');
        foreach (array_merge($accounting->getFillable(), ['created_at', 'updated_at']) as $column) {
            DB::statement("ALTER TABLE contab_movimientos ADD COLUMN $column TEXT");
        }
        $document = new \App\Inventarios\InvDocEncabezado();
        $document->forceFill($data + ['id' => 100, 'consecutivo' => 1]);
        (new \App\Inventarios\Services\InvDocumentsService())->contabilizar($document);
        $this->assertEquals(2, DB::table('contab_movimientos')->where('inv_producto_id', 384)->where('inv_bodega_id', 1)->count());
        $this->assertEquals(2, DB::table('contab_movimientos')->where('inv_producto_id', 383)->where('inv_bodega_id', 20)->count());
    }

    public function test_ingrediente_sin_receta_se_consume_en_cocina_propia_y_con_su_costo()
    {
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,1,'2026-10-07',10)");
        $lines = $this->generarLineas(new RecipeServices());
        $this->assertEquals(384, $lines[0]->inv_producto_id);
        $this->assertEquals(1, $lines[0]->inv_bodega_id);
        $this->assertEquals(2, $lines[0]->cantidad);
        $this->assertEquals(1050, $lines[0]->costo_unitario);
        $this->assertEquals(383, $lines[1]->inv_producto_id);
        $this->assertEquals(20, $lines[1]->inv_bodega_id);
    }

    public function test_ensamble_anidado_se_hace_en_cocina_del_ingrediente_por_el_faltante()
    {
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,1,'2026-10-07',1)");
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(1, $service->documents);
        $nested = $service->documents[0];
        $this->assertEquals(1, $nested['warehouse']);
        $this->assertEquals(51107, $nested['invoice']);
        $this->assertEquals(50, $nested['lines'][0]->inv_producto_id);
        $this->assertEquals(1, $nested['lines'][0]->inv_bodega_id);
        $this->assertEquals(3, $nested['lines'][0]->cantidad);
        $this->assertEquals(384, $nested['lines'][1]->inv_producto_id);
        $this->assertEquals(1, $nested['lines'][1]->cantidad);
        $this->assertEquals(1, $nested['lines'][1]->inv_bodega_id);
        $this->assertEquals(1, $lines[0]->inv_bodega_id);
    }

    public function test_no_hace_ensamble_anidado_si_cocina_tiene_existencia_suficiente()
    {
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        DB::statement("INSERT INTO inv_movimientos VALUES (1,384,1,'2026-10-07',2)");
        $service = new RecipeKitchenNestedDocumentSpy();
        $lines = $this->generarLineas($service);
        $this->assertCount(0, $service->documents);
        $this->assertEquals(1, $lines[0]->inv_bodega_id);
    }

    public function test_ingrediente_sin_receta_ni_stock_no_se_desvia_a_la_bodega_del_platillo()
    {
        $lines = $this->generarLineas(new RecipeServices());
        $this->assertEquals(1, $lines[0]->inv_bodega_id);
        $this->assertEquals(2, $lines[0]->cantidad);
    }

    public function test_faltante_sin_receta_anidada_se_imputa_a_la_cocina_del_ingrediente()
    {
        DB::statement('INSERT INTO inv_recetas_cocina VALUES (2,384,50,3)');
        DB::statement("INSERT INTO vtas_restaurante_cocinas VALUES (4,6,30,'Activo')");
        $service = new RecipeKitchenNestedDocumentSpy();
        $this->generarLineas($service);
        $this->assertCount(1, $service->documents);
        $nested = $service->documents[0]['lines'];
        $this->assertEquals(30, $nested[0]->inv_bodega_id);
        $this->assertEquals(6, $nested[0]->cantidad);
        $this->assertEquals(1, $nested[1]->inv_bodega_id);
        $this->assertEquals(2, $nested[1]->cantidad);
    }

    public function test_sin_cocina_configurada_conserva_la_bodega_del_platillo()
    {
        DB::statement('DELETE FROM vtas_restaurante_cocinas WHERE grupo_inventarios_id=5');
        $lines = $this->generarLineas(new RecipeServices());
        $this->assertEquals(20, $lines[0]->inv_bodega_id);
        $this->assertEquals(900, $lines[0]->costo_unitario);
    }
}

// Recorre el flujo real del ensamble anidado sin guardar documentos de negocio.
class RecipeKitchenNestedDocumentSpy extends RecipeServices
{
    public $documents = [];

    public function create_document_making($cantidades_facturadas, $bodega_default_id, $fecha, $parametros_config_inventarios, $descripcion_encabezado = '', $factura_pos_id = null)
    {
        $json = $this->get_lineas_registros_ensamble($cantidades_facturadas, $bodega_default_id, $parametros_config_inventarios, $fecha, $factura_pos_id);
        if (is_int($json)) {
            return 0;
        }
        $this->documents[] = [
            'warehouse' => $bodega_default_id,
            'invoice' => $factura_pos_id,
            'lines' => (new InvDocumentsLinesService())->preparar_array_lineas_registros($bodega_default_id, $json, null)
        ];
        return count($this->documents);
    }
}
