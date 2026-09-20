<?php

use App\Http\Controllers\VentasPos\ReporteController;
use App\VentasPos\Services\SalesReportShiftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SalesReportShiftLookupUser
{
    public $empresa_id = 1;
    public function can($permission) { return false; }
}

class SalesReportShiftTest extends TestCase
{
    private $previous;
    protected function setUp()
    {
        parent::setUp();
        $this->previous = DB::getDefaultConnection();
        config(['database.connections.shift_report_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''], 'cache.default' => 'array']);
        DB::setDefaultConnection('shift_report_test');
        DB::connection()->getPdo()->sqliteCreateFunction('CONCAT', function () { return implode('', func_get_args()); });
        Auth::shouldReceive('user')->andReturn(new SalesReportShiftLookupUser());
        $tables = [
            'vtas_pos_apertura_encabezados' => 'id turno_operativo_id responsable',
            'sys_campos' => 'id opciones',
            'core_turnos_operativos' => 'id core_empresa_id contexto_tipo contexto_id pdv_id estado codigo fecha_operativa abierto_en cerrado_en',
            'vtas_pos_puntos_de_ventas' => 'id descripcion cajero_id',
            'core_terceros' => 'id descripcion numero_identificacion',
            'vtas_clientes' => 'id clase_cliente_id',
            'vtas_clases_clientes' => 'id descripcion',
            'sys_tipos_transacciones' => 'id descripcion',
            'inv_grupos' => 'id descripcion', 'inv_bodegas' => 'id descripcion',
            'inv_productos' => 'id descripcion referencia unidad_medida1 unidad_medida2 inv_grupo_id prefijo_referencia_id',
            'inv_mandatario_tiene_items' => 'id item_id mandatario_id', 'inv_items_mandatarios' => 'id',
            'vtas_pos_doc_encabezados' => 'id core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id consecutivo core_tercero_id cliente_id pdv_id estado fecha forma_pago vendedor_id creado_por turno_operativo_id',
            'vtas_pos_doc_registros' => 'id vtas_pos_doc_encabezado_id inv_producto_id inv_bodega_id tasa_impuesto impuesto_id cantidad precio_total base_impuesto_total tasa_descuento valor_total_descuento',
            'vtas_doc_encabezados' => 'id core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id consecutivo turno_operativo_id',
            'vtas_movimientos' => 'id core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id consecutivo core_tercero_id cliente_id clase_cliente_id inv_producto_id inv_bodega_id fecha forma_pago vendedor_id creado_por tasa_impuesto impuesto_id cantidad precio_total base_impuesto_total tasa_descuento valor_total_descuento'
        ];
        foreach ($tables as $table => $columns) {
            DB::statement('CREATE TABLE ' . $table . ' (' . implode(', ', array_map(function ($column) { return $column . ($column === 'id' ? ' INTEGER' : ' TEXT'); }, explode(' ', $columns))) . ')');
        }
        DB::table('vtas_pos_apertura_encabezados')->insert(['id' => 1, 'turno_operativo_id' => 1, 'responsable' => 'Ana & Luis']);
        DB::table('sys_campos')->insert(['id' => 79, 'opciones' => '{"UND":"Unidad"}']);
        foreach (['vtas_pos_puntos_de_ventas', 'core_terceros', 'vtas_clases_clientes', 'inv_grupos', 'inv_bodegas'] as $table) {
            DB::table($table)->insert(['id' => 1, 'descripcion' => 'Prueba']);
        }
        DB::table('vtas_clientes')->insert(['id' => 1, 'clase_cliente_id' => 1]);
        DB::table('inv_productos')->insert(['id' => 1, 'descripcion' => 'Producto prueba', 'unidad_medida1' => 'UND', 'unidad_medida2' => '', 'inv_grupo_id' => 1]);
        foreach ([47, 52, 53] as $type) { DB::table('sys_tipos_transacciones')->insert(['id' => $type, 'descripcion' => 'Tipo ' . $type]); }
        foreach ([1 => 'CERRADO', 2 => 'AUDITADO', 3 => 'ABIERTO', 4 => 'AUDITANDO', 5 => 'CERRADO'] as $id => $state) {
            DB::table('core_turnos_operativos')->insert(['id' => $id, 'core_empresa_id' => $id === 5 ? 2 : 1, 'contexto_tipo' => 'pdv', 'contexto_id' => 1, 'pdv_id' => 1, 'estado' => $state, 'codigo' => 'T-' . $id, 'abierto_en' => '2026-09-17 20:00:00', 'cerrado_en' => '2026-09-18 04:00:00']);
        }
        foreach ([1, 2] as $shiftId) {
            DB::table('vtas_pos_doc_encabezados')->insert(['id' => $shiftId, 'core_empresa_id' => 1, 'core_tipo_transaccion_id' => 47, 'core_tipo_doc_app_id' => 1, 'consecutivo' => $shiftId, 'core_tercero_id' => 1, 'cliente_id' => 1, 'pdv_id' => 1, 'estado' => 'Pendiente', 'fecha' => '2026-09-17', 'forma_pago' => 'contado', 'creado_por' => 'cajero@test', 'turno_operativo_id' => $shiftId]);
            DB::table('vtas_pos_doc_registros')->insert(['id' => $shiftId, 'vtas_pos_doc_encabezado_id' => $shiftId, 'inv_producto_id' => 1, 'inv_bodega_id' => 1, 'tasa_impuesto' => 19, 'cantidad' => 1, 'precio_total' => 100, 'base_impuesto_total' => 100 / 1.19]);
            DB::table('vtas_doc_encabezados')->insert(['id' => $shiftId, 'core_empresa_id' => 1, 'core_tipo_transaccion_id' => 52, 'core_tipo_doc_app_id' => 1, 'consecutivo' => $shiftId, 'turno_operativo_id' => $shiftId]);
            // Conversión posterior al cierre: debe conservarse dentro del turno original.
            DB::table('vtas_movimientos')->insert(['id' => $shiftId, 'core_empresa_id' => 1, 'core_tipo_transaccion_id' => 52, 'core_tipo_doc_app_id' => 1, 'consecutivo' => $shiftId, 'core_tercero_id' => 1, 'cliente_id' => 1, 'clase_cliente_id' => 1, 'inv_producto_id' => 1, 'inv_bodega_id' => 1, 'fecha' => '2026-09-19', 'forma_pago' => 'contado', 'creado_por' => 'otro@test', 'tasa_impuesto' => 19, 'cantidad' => 1, 'precio_total' => 200, 'base_impuesto_total' => 200 / 1.19]);
        }
    }
    protected function tearDown()
    {
        DB::purge('shift_report_test'); DB::setDefaultConnection($this->previous);
        parent::tearDown();
    }
    private function report(array $data = [])
    {
        return (new ReporteController())->movimientos_ventas(new Request(array_merge([
            'turno_operativo_id' => 1, 'agrupar_por' => 'inv_bodega_id', 'estado_facturas' => 'Todos',
            'iva_incluido' => 1, 'detalla_productos' => 0, 'pdv_id' => 0, 'reporte_instancia' => '{"id":54}'
        ], $data)));
    }
    public function test_turno_no_exige_fechas_y_conserva_totales_en_todas_las_agrupaciones()
    {
        foreach (['pdv_id', 'inv_grupo_id', 'inv_bodega_id', 'inv_producto_id', 'cliente_id', 'forma_pago', 'tasa_impuesto', 'core_tipo_transaccion_id'] as $group) {
            foreach ([0, 1] as $detail) {
                foreach ([0, 1] as $tax) {
                    $html = $this->report(['agrupar_por' => $group, 'detalla_productos' => $detail, 'iva_incluido' => $tax]);
                    $this->assertContains($tax ? '300,00' : '252,10', $html);
                    $this->assertContains('Responsable:</b> Ana &amp; Luis', $html);
                    $this->assertContains('2026-09-17 20:00:00', $html);
                    $this->assertContains('2026-09-18 04:00:00', $html);
                }
            }
        }
    }
    public function test_estados_pdv_y_turnos_auditados()
    {
        $this->assertContains('100,00', $this->report(['estado_facturas' => 'Pendiente']));
        $this->assertContains('200,00', $this->report(['estado_facturas' => 'Contabilizado']));
        $this->assertContains('T-2', $this->report(['turno_operativo_id' => 2, 'pdv_id' => 1]));
        $this->assertSame(422, $this->report(['pdv_id' => 2])->getStatusCode());
        foreach ([3, 4, 5, 999] as $id) {
            $this->assertSame(422, $this->report(['turno_operativo_id' => $id])->getStatusCode());
        }
        $this->assertCount(2, (new SalesReportShiftService())->available(1)->get());
    }
    public function test_fechas_manuales_siguen_filtrando_sin_turno()
    {
        $html = $this->report(['turno_operativo_id' => '', 'fecha_desde' => '2026-09-17', 'fecha_hasta' => '2026-09-17']);
        $this->assertContains('200,00', $html);
        $this->assertNotContains('<b>Turno:</b>', $html);
    }
    public function test_selector_solo_ofrece_turnos_de_la_empresa_cerrados_o_auditados()
    {
        config(['turnos.modules.tesoreria.integrated' => true]);
        $controller = new \App\Http\Controllers\Core\TurnoOperativoLookupController(
            Mockery::mock('App\Core\Services\TurnoModeResolver')
        );
        $params = ['modulo' => 'tesoreria', 'reporte' => 'pos_movimientos_ventas', 'texto_busqueda' => 'T-', 'pdv_id' => 0];
        $html = $controller->suggestions(new Request($params))->getContent();
        $this->assertContains('T-1', $html);
        $this->assertContains('T-2', $html);
        $this->assertContains('data-turno-opening-at="2026-09-17 20:00:00"', $html);
        $this->assertContains('data-turno-closing-at="2026-09-18 04:00:00"', $html);
        foreach (['T-3', 'T-4', 'T-5'] as $code) { $this->assertNotContains($code, $html); }
        $params['pdv_id'] = 99;
        $this->assertNotContains('data-registro_id', $controller->suggestions(new Request($params))->getContent());
        $params['pdv_id'] = 0; $params['texto_busqueda'] = 'T-2';
        $html = $controller->suggestions(new Request($params))->getContent();
        $this->assertContains('T-2', $html);
        $this->assertNotContains('T-1', $html);
        $field = view('ventas_pos.reportes.filtro_turno_operativo')->render();
        $this->assertContains('text_input_sugerencias', $field);
        $this->assertContains('turnos/operativos/sugerencias', $field);
        $this->assertNotContains('<select', $field);
    }

    public function test_orden_descendente_por_venta_total_se_conserva()
    {
        DB::table('inv_bodegas')->insert(['id' => 2, 'descripcion' => 'Bodega menor']);
        DB::table('vtas_pos_doc_encabezados')->where('id', 2)->update(['turno_operativo_id' => 1]);
        DB::table('vtas_pos_doc_registros')->where('id', 2)->update(['inv_bodega_id' => 2]);
        $html = $this->report();
        $this->assertContains('400,00', $html);
        $this->assertTrue(strpos($html, '> Prueba </td>') < strpos($html, '> Bodega menor </td>'));
        $this->assertContains('Bodega menor', $html);
    }
}
