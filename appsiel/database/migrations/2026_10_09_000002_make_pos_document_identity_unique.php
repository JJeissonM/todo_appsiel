<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakePosDocumentIdentityUnique extends Migration
{
    const INDEX_NAME = 'vtas_pos_empresa_transaccion_documento_consecutivo_unique';

    public function up()
    {
        if (!Schema::hasTable('vtas_pos_doc_encabezados') || $this->hasIndex()) {
            return;
        }
        $columns = array('core_empresa_id', 'core_tipo_transaccion_id', 'core_tipo_doc_app_id', 'consecutivo');
        $duplicate = DB::table('vtas_pos_doc_encabezados')->select($columns)
            ->groupBy($columns)->havingRaw('COUNT(*) > 1')->first();
        if ($duplicate !== null) {
            throw new RuntimeException('Concilie las facturas POS duplicadas por empresa/transacción/tipo/consecutivo antes de activar su unicidad. No se renumeraron documentos.');
        }

        Schema::table('vtas_pos_doc_encabezados', function (Blueprint $table) use ($columns) {
            $table->unique($columns, self::INDEX_NAME);
        });
    }

    public function down()
    {
        if (Schema::hasTable('vtas_pos_doc_encabezados') && $this->hasIndex()) {
            Schema::table('vtas_pos_doc_encabezados', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }
    }

    private function hasIndex()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('vtas_pos_doc_encabezados')") as $index) {
                if ($index->name === self::INDEX_NAME) {
                    return true;
                }
            }
            return false;
        }
        return count(DB::select('SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1', array('vtas_pos_doc_encabezados', self::INDEX_NAME))) > 0;
    }
}
