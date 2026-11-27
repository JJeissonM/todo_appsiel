<?php

use App\VentasPos\FacturaPos;
use Illuminate\Database\Capsule\Manager as Capsule;

class PosInvoiceExactSearchOrderTest extends PHPUnit_Framework_TestCase
{
    public function test_exact_number_precedes_partial_matches_without_excluding_other_series()
    {
        $capsule = new Capsule();
        $capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
        $connection = $capsule->getConnection();
        $connection->getSchemaBuilder()->create('vtas_pos_doc_encabezados', function ($t) {
            $t->integer('id');
            $t->integer('consecutivo');
        });
        $connection->table('vtas_pos_doc_encabezados')->insert([
            ['id' => 1, 'consecutivo' => 61757],
            ['id' => 2, 'consecutivo' => 757],
            ['id' => 3, 'consecutivo' => 60757],
            ['id' => 4, 'consecutivo' => 757],
        ]);
        $method = new ReflectionMethod(FacturaPos::class, 'priorizarConsecutivoExacto');
        $method->setAccessible(true);
        foreach (['757', ' 757 ', '000757', '', 'cliente', "757' OR 1=1"] as $search) {
            $query = $connection->table('vtas_pos_doc_encabezados');
            $method->invoke(null, $query, $search);
            $rows = $query->orderBy('consecutivo', 'desc')->orderBy('id', 'desc')->get();
            $ids = array_map(function ($row) { return (int)$row->id; }, (array)$rows);
            $numeric = in_array($search, ['757', ' 757 ', '000757']);
            $this->assertSame($numeric ? [4, 2, 1, 3] : [1, 3, 4, 2], $ids);
            $this->assertSame($numeric ? [trim($search)] : [], $query->getBindings());
        }
    }
}
