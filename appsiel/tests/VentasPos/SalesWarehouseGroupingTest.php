<?php

use App\Http\Controllers\VentasPos\ReporteController;
use App\VentasPos\Movimiento;
use App\Ventas\VtasMovimiento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SalesWarehouseGroupingTest extends TestCase
{
    public function test_ambas_consultas_seleccionan_la_bodega_de_la_linea()
    {
        Auth::shouldReceive('user')->andReturn((object)['empresa_id' => 1]);
        $queries = DB::connection()->pretend(function () {
            Movimiento::get_movimiento_ventas('2026-09-01', '2026-09-15', 'inv_bodega_id', 'Todos', null, 0);
            VtasMovimiento::get_movimiento_ventas_por_transaccion('2026-09-01', '2026-09-15', 'inv_bodega_id', [52]);
        });
        $this->assertCount(2, $queries);
        $this->assertContains('`vtas_pos_doc_registros`.`inv_bodega_id`', $queries[0]['query']);
        $this->assertContains('`vtas_movimientos`.`inv_bodega_id`', $queries[1]['query']);
        foreach ($queries as $query) {
            $this->assertContains('left join `inv_bodegas`', $query['query']);
            $this->assertContains('`inv_bodegas`.`descripcion` as `bodega_descripcion`', $query['query']);
        }
    }

    public function test_totales_por_bodega_con_y_sin_iva_y_sin_bodega()
    {
        $lines = collect([
            (object)['inv_bodega_id' => 1, 'bodega_descripcion' => 'Principal', 'cantidad' => 2, 'precio_total' => 238, 'base_impuesto_total' => 200],
            (object)['inv_bodega_id' => 1, 'bodega_descripcion' => 'Principal', 'cantidad' => 1, 'precio_total' => 119, 'base_impuesto_total' => 100],
            (object)['inv_bodega_id' => 2, 'bodega_descripcion' => 'Sucursal', 'cantidad' => 4, 'precio_total' => 476, 'base_impuesto_total' => 400],
            (object)['inv_bodega_id' => 0, 'bodega_descripcion' => null, 'cantidad' => 1, 'precio_total' => 50, 'base_impuesto_total' => 50]
        ]);
        foreach ($lines as $line) {
            $line->inv_producto_id = 10;
            $line->item = new class {
                public function get_value_to_show() { return 'Producto de prueba'; }
            };
        }
        $controller = new ReporteController();
        foreach ([0, 1] as $withTax) {
            $rows = $controller->get_array_lista_registros([], $lines->groupBy('inv_bodega_id'), 'inv_bodega_id', 0, $withTax, 'POS', null);
            $this->assertCount(3, $rows);
            $this->assertSame('Principal', $rows[0]['descripcion']);
            $this->assertEquals(3, $rows[0]['cantidad']);
            $this->assertEquals($withTax ? 357 : 300, $rows[0]['precio']);
            $this->assertEquals($withTax ? 119 : 100, $rows[0]['precio_promedio']);
            $this->assertSame('Sin bodega', $rows[2]['descripcion']);
        }
        $rows = $controller->get_array_lista_registros([], $lines->groupBy('inv_bodega_id'), 'inv_bodega_id', 1, 1, 'POS', null);
        $this->assertCount(1, $rows[0]['array_detalle_productos']);
        $this->assertEquals(357, $rows[0]['array_detalle_productos'][0]['precio_item']);
        $html = view('ventas_pos.reportes.reporte_ventas_ordenado', [
            'array_lista' => $rows, 'agrupar_por' => 'inv_bodega_id',
            'mensaje' => 'IVA incluido', 'iva_incluido' => 1, 'detalla_productos' => 1, 'pdv' => null
        ])->render();
        $this->assertContains('Bodega', $html);
        $this->assertContains('Principal', $html);
        $this->assertContains('Producto de prueba', $html);
    }
}
