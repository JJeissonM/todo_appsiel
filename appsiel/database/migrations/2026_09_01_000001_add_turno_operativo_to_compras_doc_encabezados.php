<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTurnoOperativoToComprasDocEncabezados extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_encabezados')
            || Schema::hasColumn('compras_doc_encabezados', 'turno_operativo_id')) {
            return;
        }

        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            // Nullable conserva documentos históricos y el modo TRADICIONAL.
            $table->unsignedInteger('turno_operativo_id')->nullable();
            $table->index('turno_operativo_id', 'compras_doc_enc_turno_idx');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_encabezados')
            || !Schema::hasColumn('compras_doc_encabezados', 'turno_operativo_id')) {
            return;
        }

        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            if ($this->hasIndex('compras_doc_encabezados', 'compras_doc_enc_turno_idx')) {
                $table->dropIndex('compras_doc_enc_turno_idx');
            }
            $table->dropColumn('turno_operativo_id');
        });
    }

    protected function hasIndex($tableName, $indexName)
    {
        return count(DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            array($tableName, $indexName)
        )) > 0;
    }
}
