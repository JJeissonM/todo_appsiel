<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeDocumentCountersUnique extends Migration
{
    const INDEX_NAME = 'core_consecutivos_empresa_documento_unique';

    public function up()
    {
        if (!Schema::hasTable('core_consecutivos_documentos') || $this->hasIndex()) {
            return;
        }
        $duplicate = DB::table('core_consecutivos_documentos')
            ->select('core_empresa_id', 'core_documento_app_id')
            ->groupBy('core_empresa_id', 'core_documento_app_id')
            ->havingRaw('COUNT(*) > 1')->first();
        if ($duplicate !== null) {
            throw new RuntimeException('Concilie los contadores duplicados de empresa/documento antes de activar su unicidad. No se modificaron los contadores.');
        }

        Schema::table('core_consecutivos_documentos', function (Blueprint $table) {
            $table->unique(array('core_empresa_id', 'core_documento_app_id'), self::INDEX_NAME);
        });
    }

    public function down()
    {
        if (Schema::hasTable('core_consecutivos_documentos') && $this->hasIndex()) {
            Schema::table('core_consecutivos_documentos', function (Blueprint $table) {
                $table->dropUnique(self::INDEX_NAME);
            });
        }
    }

    private function hasIndex()
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('core_consecutivos_documentos')") as $index) {
                if ($index->name === self::INDEX_NAME) {
                    return true;
                }
            }
            return false;
        }
        return count(DB::select('SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1', array('core_consecutivos_documentos', self::INDEX_NAME))) > 0;
    }
}
