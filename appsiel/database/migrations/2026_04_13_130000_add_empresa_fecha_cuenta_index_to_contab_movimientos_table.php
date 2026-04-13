<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEmpresaFechaCuentaIndexToContabMovimientosTable extends Migration
{
    protected $indexName = 'contab_mov_empresa_fecha_cuenta_idx';

    public function up()
    {
        if (!Schema::hasTable('contab_movimientos')) {
            return;
        }

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'contab_movimientos')
            ->where('index_name', $this->indexName)
            ->exists();

        if (!$indexExists) {
            DB::statement(
                'ALTER TABLE contab_movimientos ADD INDEX ' . $this->indexName . ' (core_empresa_id, fecha, contab_cuenta_id)'
            );
        }
    }

    public function down()
    {
        if (!Schema::hasTable('contab_movimientos')) {
            return;
        }

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'contab_movimientos')
            ->where('index_name', $this->indexName)
            ->exists();

        if ($indexExists) {
            DB::statement('ALTER TABLE contab_movimientos DROP INDEX ' . $this->indexName);
        }
    }
}
