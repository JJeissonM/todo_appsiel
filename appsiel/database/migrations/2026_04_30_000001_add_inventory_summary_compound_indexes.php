<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddInventorySummaryCompoundIndexes extends Migration
{
    protected $movimientosIndexName = 'inv_mov_empresa_bodega_producto_fecha_idx';
    protected $minStocksIndexName = 'inv_min_stock_bodega_producto_idx';

    public function up()
    {
        $this->addIndexIfMissing(
            'inv_movimientos',
            $this->movimientosIndexName,
            'ALTER TABLE inv_movimientos ADD INDEX ' . $this->movimientosIndexName . ' (core_empresa_id, inv_bodega_id, inv_producto_id, fecha)'
        );

        $this->addIndexIfMissing(
            'inv_min_stocks',
            $this->minStocksIndexName,
            'ALTER TABLE inv_min_stocks ADD INDEX ' . $this->minStocksIndexName . ' (inv_bodega_id, inv_producto_id)'
        );
    }

    public function down()
    {
        $this->dropIndexIfExists('inv_min_stocks', $this->minStocksIndexName);
        $this->dropIndexIfExists('inv_movimientos', $this->movimientosIndexName);
    }

    private function addIndexIfMissing($tableName, $indexName, $statement)
    {
        if (!Schema::hasTable($tableName) || $this->indexExists($tableName, $indexName)) {
            return;
        }

        DB::statement($statement);
    }

    private function dropIndexIfExists($tableName, $indexName)
    {
        if (!Schema::hasTable($tableName) || !$this->indexExists($tableName, $indexName)) {
            return;
        }

        DB::statement('ALTER TABLE ' . $tableName . ' DROP INDEX ' . $indexName);
    }

    private function indexExists($tableName, $indexName)
    {
        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->exists();
    }
}
