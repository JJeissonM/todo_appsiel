<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEmpresaDocLookupIndexToTesoMovimientosTable extends Migration
{
    protected $indexName = 'teso_mov_empresa_doc_lookup_idx';

    public function up()
    {
        if (!Schema::hasTable('teso_movimientos')) {
            return;
        }

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'teso_movimientos')
            ->where('index_name', $this->indexName)
            ->exists();

        if (!$indexExists) {
            DB::statement(
                'ALTER TABLE teso_movimientos ADD INDEX ' . $this->indexName . ' (core_empresa_id, core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo)'
            );
        }
    }

    public function down()
    {
        if (!Schema::hasTable('teso_movimientos')) {
            return;
        }

        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'teso_movimientos')
            ->where('index_name', $this->indexName)
            ->exists();

        if ($indexExists) {
            DB::statement('ALTER TABLE teso_movimientos DROP INDEX ' . $this->indexName);
        }
    }
}
