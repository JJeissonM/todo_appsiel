<?php

use App\Core\Services\TransactionIndexOrder;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;

class TransactionIndexOrderTest extends PHPUnit_Framework_TestCase
{
    private $previousContainer;
    private $container;
    private $connection;
    private $tables = [
        'vtas_doc_encabezados', 'vtas_pos_doc_encabezados',
        'cxc_doc_encabezados', 'cxp_doc_encabezados', 'teso_doc_encabezados',
    ];

    protected function setUp()
    {
        $this->previousContainer = Container::getInstance() ?: new Container();
        $this->container = new Container();
        $this->container->instance('config', new Repository());
        Container::setInstance($this->container);
        $capsule = new Capsule($this->container);
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $this->connection = $capsule->getConnection();
        foreach ($this->tables as $table) {
            $this->connection->getSchemaBuilder()->create($table, function ($t) {
                $t->increments('id');
                $t->integer('consecutivo');
                $t->date('fecha');
                $t->dateTime('created_at');
            });
            $this->connection->table($table)->insert([
                ['id' => 1, 'consecutivo' => 900, 'fecha' => '2026-10-01', 'created_at' => '2026-11-10 15:00:00'],
                ['id' => 2, 'consecutivo' => 100, 'fecha' => '2026-11-10', 'created_at' => '2026-11-10 09:00:00'],
                ['id' => 3, 'consecutivo' => 200, 'fecha' => '2026-11-10', 'created_at' => '2026-11-10 10:00:00'],
                ['id' => 4, 'consecutivo' => 200, 'fecha' => '2026-11-10', 'created_at' => '2026-11-10 10:00:00'],
            ]);
        }
    }

    protected function tearDown()
    {
        Container::setInstance($this->previousContainer);
    }

    public function testMissingDefaultAndInvalidConfigurationsOrderByConsecutive()
    {
        foreach ([null, 'consecutivo', 'invalid'] as $value) {
            if ($value !== null) $this->container['config']->set('configuracion.orden_transacciones_index', $value);
            foreach ($this->tables as $table) {
                $this->assertSame([1, 4, 3, 2], $this->orderedIds($table));
            }
        }
    }

    public function testDocumentDatePrecedesCreationTimeWithStableTiesAndPagination()
    {
        $this->container['config']->set('configuracion.orden_transacciones_index', 'fecha');
        foreach ($this->tables as $table) {
            $this->assertSame([4, 3, 2, 1], $this->orderedIds($table));
            $this->assertSame([2, 1], $this->orderedIds($table, 2));
        }
    }

    public function testOtherModulesCannotUseTheSetting()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        TransactionIndexOrder::sql('compras_doc_encabezados');
    }

    private function orderedIds($table, $offset = 0)
    {
        $rows = $this->connection->table($table)->orderByRaw(TransactionIndexOrder::sql($table))
            ->offset($offset)->limit(4)->get();
        return array_map(function ($row) { return (int)$row->id; }, $rows);
    }
}
