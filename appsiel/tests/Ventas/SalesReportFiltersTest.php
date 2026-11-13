<?php

use Illuminate\Container\Container;
use Illuminate\Config\Repository;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use App\Ventas\VtasMovimiento;

class SalesReportItemFixture
{
    public $descripcion;
    public function __construct($name) { $this->descripcion = $name; }
    public function get_value_to_show() { return 'Producto '.$this->descripcion; }
}

class SalesReportFiltersTest extends PHPUnit_Framework_TestCase
{
    public function testRenderedReportOrdersBySalesNumericIdAndProductNameWithoutChangingTotals()
    {
        $movimiento = collect([]);
        foreach ([[10, 'Alfa', 100], [2, 'Zeta', 300]] as $values) {
            $movimiento->put($values[0], collect([(object)[
                'inv_producto_id' => $values[0], 'item' => new SalesReportItemFixture($values[1]),
                'cantidad' => 1, 'precio_total' => $values[2], 'base_impuesto_total' => $values[2],
            ]]));
        }
        $agrupar_por = 'inv_producto_id';
        $mensaje = 'Prueba';
        $iva_incluido = 1;
        $detalla_productos = 1;
        $blade = new Illuminate\View\Compilers\BladeCompiler(new Illuminate\Filesystem\Filesystem(), sys_get_temp_dir());
        $compiled = $blade->compileString(file_get_contents(__DIR__.'/../../resources/views/ventas/reportes/reporte_ventas_ordenado.blade.php'));
        foreach ([null, 'mayor_vendido', 'id', 'alfabetico'] as $ordenar_por) {
            ob_start();
            eval('?>'.$compiled);
            $html = ob_get_clean();
            $first = $ordenar_por === 'alfabetico' ? 'Alfa' : 'Zeta';
            $last = $ordenar_por === 'alfabetico' ? 'Zeta' : 'Alfa';
            $this->assertTrue(strpos($html, 'Producto '.$first) < strpos($html, 'Producto '.$last));
            $this->assertContains('$400,00', $html);
            $this->assertContains('$200,00', $html); // Promedio global.
        }
    }

    public function testBothStandardSalesSourcesFilterPdvAndKeepCompanyAndDateBoundaries()
    {
        $oldApp = Facade::getFacadeApplication();
        $oldContainer = Container::getInstance() ?: new Container();
        $oldResolver = Model::getConnectionResolver();
        $container = new Container();
        $container->instance('config', new Repository());
        $container->instance('auth', new class {
            public function user() { return (object)['empresa_id' => 1]; }
        });
        $capsule = new Capsule($container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $capsule->bootEloquent();
        $container->instance('db', $capsule->getDatabaseManager());
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        Container::setInstance($container);
        try {
            $db = $capsule->getConnection();
            $db->getPdo()->sqliteCreateFunction('CONCAT', function () { return implode('', func_get_args()); });
            $tables = [
                'inv_productos' => 'descripcion unidad_medida1 unidad_medida2 referencia inv_grupo_id',
                'inv_bodegas' => 'descripcion', 'inv_grupos' => 'descripcion',
                'core_terceros' => 'descripcion numero_identificacion',
                'vtas_clases_clientes' => 'descripcion', 'sys_tipos_transacciones' => 'descripcion',
                'vtas_clientes' => 'lista_precios_id',
                'vtas_pos_doc_encabezados' => 'core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id consecutivo pdv_id',
                'vtas_movimientos' => 'fecha core_empresa_id core_tipo_transaccion_id core_tipo_doc_app_id consecutivo inv_producto_id core_tercero_id clase_cliente_id cliente_id tasa_impuesto forma_pago vendedor_id impuesto_id cantidad precio_total tasa_descuento valor_total_descuento inv_bodega_id base_impuesto_total creado_por',
            ];
            foreach ($tables as $name => $columns) {
                $db->getSchemaBuilder()->create($name, function ($table) use ($columns) {
                    $table->increments('id');
                    foreach (explode(' ', $columns) as $column) $table->text($column)->nullable();
                });
            }
            $base = ['fecha' => '2026-11-13', 'core_empresa_id' => 1, 'core_tipo_transaccion_id' => 23,
                'core_tipo_doc_app_id' => 1, 'inv_producto_id' => 1, 'cantidad' => 1, 'tasa_impuesto' => 0,
                'cliente_id' => 5, 'precio_total' => 100];
            foreach ([[10, 1, 1], [11, 2, 1], [12, 1, 99]] as $row) {
                $db->table('vtas_movimientos')->insert(array_merge($base, ['consecutivo' => $row[0], 'core_empresa_id' => $row[2]]));
                $db->table('vtas_pos_doc_encabezados')->insert(['core_empresa_id' => $row[2],
                    'core_tipo_transaccion_id' => 23, 'core_tipo_doc_app_id' => 1, 'consecutivo' => $row[0], 'pdv_id' => $row[1]]);
            }
            foreach ([null, 0, '', 1, 2, 999] as $pdv) {
                $expected = ($pdv === null || $pdv === 0 || $pdv === '') ? 200 : ($pdv === 999 ? 0 : 100);
                $sales = VtasMovimiento::get_movimiento_ventas('2026-11-13', '2026-11-13', 'inv_producto_id', null, 5, $pdv);
                $combined = VtasMovimiento::get_movimiento_ventas_por_transaccion('2026-11-13', '2026-11-13', 'inv_producto_id', [23], null, 0, $pdv);
                foreach ([$sales, $combined] as $groups) {
                    $total = 0;
                    foreach ($groups as $group) $total += $group->sum('precio_total');
                    $this->assertEquals($expected, $total);
                }
            }
        } finally {
            if ($oldResolver) {
                Model::setConnectionResolver($oldResolver);
            } else {
                Model::unsetConnectionResolver();
            }
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($oldApp);
            Container::setInstance($oldContainer);
        }
    }
}
