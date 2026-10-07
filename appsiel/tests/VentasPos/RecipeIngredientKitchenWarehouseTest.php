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
            "INSERT INTO vtas_restaurante_cocinas VALUES (2,4,20,'Activo'),(3,5,1,'Activo')",
            "INSERT INTO inv_motivos VALUES (3,'Consumo'),(4,'Producto final')",
            // La bodega del platillo tiene stock abundante: no debe ganar prioridad.
            "INSERT INTO inv_movimientos VALUES (1,384,20,'2026-10-07',100)",
            'INSERT INTO inv_costo_prom_productos VALUES (384,20,900),(384,1,1050)'
        ] as $sql) {
            DB::statement($sql);
        }
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

    public function test_ingredientes_del_ensamble_anidado_resuelven_su_propia_cocina()
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
