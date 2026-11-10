<?php

use App\Ventas\Services\ProfitabilityReportService as Report;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\DB;

class ProfitabilityReportTest extends PHPUnit_Framework_TestCase
{
    public function testDefaultAndUnknownOrdersPreserveLegacySortingIncludingTies()
    {
        $rows = [
            ['descripcion' => 'Zeta', 'precio' => 150, 'orden_id' => 2, 'orden_nombre' => 'Zeta'],
            ['descripcion' => 'Beta', 'precio' => 300, 'orden_id' => 10, 'orden_nombre' => 'Beta'],
            ['descripcion' => 'Alfa', 'precio' => 300, 'orden_id' => 1, 'orden_nombre' => 'Alfa']
        ];
        $legacy = $rows;
        foreach ($legacy as &$row) {
            unset($row['orden_id'], $row['orden_nombre']);
        }
        unset($row);
        $prices = array_column($legacy, 'precio');
        array_multisort($prices, SORT_DESC, $legacy);
        foreach ([null, '', 'mayor_vendido', 'invalido'] as $order) {
            $this->assertSame($legacy, Report::sortRows($rows, $order));
        }
        $this->assertSame([], Report::sortRows([], null));
    }

    public function testIdIsNumericAndAlphabeticalUsesNameRatherThanProductCode()
    {
        $rows = [
            ['precio' => 100, 'orden_id' => 10, 'orden_nombre' => 'alfa'],
            ['precio' => 200, 'orden_id' => 2, 'orden_nombre' => 'Beta']
        ];
        $this->assertSame([2, 10], array_column(Report::sortRows($rows, 'id'), 'orden_id'));
        $this->assertSame([10, 2], array_column(Report::sortRows($rows, 'alfabetico'), 'orden_id'));
    }

    public function testPdvFilteringMatchesWholeDocumentIdentityWithoutDuplicateSales()
    {
        $capsule = new Capsule(new Container());
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $connection = $capsule->getConnection();
        foreach (['vtas_movimientos', 'vtas_pos_doc_encabezados'] as $table) {
            $connection->getSchemaBuilder()->create($table, function ($t) use ($table) {
                $t->increments('id');
                foreach (['core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo'] as $column) {
                    $t->integer($column);
                }
                if ($table === 'vtas_pos_doc_encabezados') $t->integer('pdv_id');
            });
        }
        $identity = ['core_empresa_id' => 1, 'core_tipo_transaccion_id' => 47,
            'core_tipo_doc_app_id' => 1, 'consecutivo' => 100];
        $connection->table('vtas_movimientos')->insert($identity);
        $connection->table('vtas_movimientos')->insert(array_merge($identity, ['consecutivo' => 101]));
        $header = array_merge($identity, ['pdv_id' => 2]);
        $connection->table('vtas_pos_doc_encabezados')->insert([$header, $header,
            array_merge($header, ['consecutivo' => 101, 'pdv_id' => 3]),
            array_merge($header, ['consecutivo' => 101, 'core_empresa_id' => 99])]);
        $this->assertCount(2, $connection->table('vtas_movimientos')->get());
        $query = Report::filterByPdv($connection->table('vtas_movimientos'), 2);
        $this->assertSame([100], array_map(function ($row) { return (int)$row->consecutivo; }, $query->get()));
        $this->assertCount(0, Report::filterByPdv($connection->table('vtas_movimientos'), 999)->get());
    }

    public function testMigrationIsIdempotentAndRollbackPreservesExistingFields()
    {
        $old = Facade::getFacadeApplication();
        $container = new Container();
        $capsule = new Capsule($container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $container->instance('db', $capsule->getDatabaseManager());
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        try {
            $schema = $capsule->getConnection()->getSchemaBuilder();
            $schema->create('sys_reportes', function ($t) {
                $t->increments('id'); $t->string('url_form_action');
            });
            $schema->create('sys_campos', function ($t) {
                $t->increments('id');
                foreach (['name', 'descripcion', 'opciones', 'value', 'tipo', 'atributos', 'definicion', 'created_at', 'updated_at'] as $c) $t->text($c)->nullable();
                foreach (['requerido', 'editable', 'unico'] as $c) $t->integer($c)->nullable();
            });
            $schema->create('sys_reporte_tiene_campos', function ($t) {
                foreach (['core_reporte_id', 'core_campo_id', 'orden'] as $c) $t->integer($c);
            });
            $schema->create('sys_modelo_tiene_campos', function ($t) { $t->integer('core_campo_id'); });
            DB::table('sys_reportes')->insert(['id' => 1, 'url_form_action' => 'vtas_reporte_rentabilidad']);
            DB::table('sys_reportes')->insert(['id' => 2, 'url_form_action' => 'otro_reporte']);
            DB::table('sys_campos')->insert(['id' => 1, 'name' => 'fecha_desde']);
            DB::table('sys_reporte_tiene_campos')->insert(['core_reporte_id' => 1, 'core_campo_id' => 1, 'orden' => 5]);
            require_once __DIR__.'/../../database/migrations/2026_11_10_000001_add_sales_profitability_report_filters.php';
            $migration = new AddSalesProfitabilityReportFilters();
            $migration->up();
            $migration->up();
            $this->assertSame(3, DB::table('sys_campos')->count());
            $this->assertSame(3, DB::table('sys_reporte_tiene_campos')->count());
            $this->assertEquals(7, DB::table('sys_reporte_tiene_campos')->max('orden'));
            $this->assertSame('mayor_vendido', DB::table('sys_campos')->where('name', 'ordenar_por')->value('value'));
            $migration->down();
            $this->assertEquals([1], DB::table('sys_campos')->lists('id'));
            $this->assertSame(1, DB::table('sys_reporte_tiene_campos')->count());
        } finally {
            Facade::clearResolvedInstances();
            Facade::setFacadeApplication($old);
        }
    }
}
