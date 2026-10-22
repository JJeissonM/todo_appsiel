<?php

use App\Inventarios\Services\ItemsFiltersServices;
use Illuminate\Support\Facades\DB;

class ItemsFiltersServicesTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        config(['database.connections.items_filters_test' => ['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
        DB::setDefaultConnection('items_filters_test');
        DB::statement('CREATE TABLE inv_productos (id INTEGER, descripcion TEXT, inv_grupo_id INTEGER, estado TEXT)');
        DB::statement("INSERT INTO inv_productos VALUES (1,'Agua',2,'Activo'),(2,'Jugo',3,'Inactivo')");
    }

    protected function tearDown()
    {
        DB::disconnect('items_filters_test');
        parent::tearDown();
    }

    protected function filtros($prefijo = '', $tipo = '')
    {
        return (object)['item_id'=>'','grupo_inventario_id'=>'','prefijo_referencia_id'=>$prefijo,'tipo_prenda_id'=>$tipo];
    }

    public function test_filtros_vacios_funcionan_sin_tablas_de_indumentaria()
    {
        $items = (new ItemsFiltersServices())->get_listado_de_items($this->filtros(), true);
        $this->assertCount(1, $items);
        $this->assertEquals(1, $items->first()->id);
    }

    public function test_filtro_seleccionado_incompatible_produce_error_controlado()
    {
        DB::statement('CREATE TABLE inv_mandatario_tiene_items (item_id INTEGER, mandatario_id INTEGER)');
        DB::statement('CREATE TABLE inv_items_mandatarios (id INTEGER, descripcion TEXT)');
        try {
            (new ItemsFiltersServices())->get_listado_de_items($this->filtros('1'));
            $this->fail('El filtro no debe ignorarse silenciosamente.');
        } catch (InvalidArgumentException $e) {
            $this->assertContains('inv_items_mandatarios.prefijo_referencia_id', $e->getMessage());
        }
    }

    public function test_tipo_prenda_funciona_sin_columna_de_prefijo()
    {
        DB::statement('CREATE TABLE inv_mandatario_tiene_items (item_id INTEGER, mandatario_id INTEGER)');
        DB::statement('CREATE TABLE inv_items_mandatarios (id INTEGER, descripcion TEXT, tipo_prenda_id INTEGER)');
        DB::statement('CREATE TABLE inv_indum_tipos_prendas (id INTEGER)');
        DB::statement('INSERT INTO inv_indum_tipos_prendas VALUES (4)');
        DB::statement("INSERT INTO inv_items_mandatarios VALUES (10,'Prenda',4)");
        DB::statement('INSERT INTO inv_mandatario_tiene_items VALUES (1,10)');
        $items = (new ItemsFiltersServices())->get_listado_de_items($this->filtros('', '4'));
        $this->assertCount(1, $items);
        $this->assertSame('Prenda', $items->first()->descripcion_prenda);
    }
}
