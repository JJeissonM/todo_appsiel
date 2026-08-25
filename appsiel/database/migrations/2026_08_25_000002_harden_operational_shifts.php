<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class HardenOperationalShifts extends Migration
{
    public function up()
    {
        if (Schema::hasTable('hotel_order_lines') && !Schema::hasColumn('hotel_order_lines', 'turno_operativo_id')) {
            Schema::table('hotel_order_lines', function (Blueprint $table) {
                $table->unsignedInteger('turno_operativo_id')->nullable();
                $table->index('turno_operativo_id', 'hotel_order_lines_turno_idx');
            });
        }

        if (Schema::hasTable('core_turno_eventos') && !$this->hasIndex('core_turno_eventos', 'turno_evento_timeline_idx')) {
            Schema::table('core_turno_eventos', function (Blueprint $table) {
                $table->index(array('turno_operativo_id', 'created_at'), 'turno_evento_timeline_idx');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('core_turno_eventos') && $this->hasIndex('core_turno_eventos', 'turno_evento_timeline_idx')) {
            Schema::table('core_turno_eventos', function (Blueprint $table) {
                $table->dropIndex('turno_evento_timeline_idx');
            });
        }
        if (Schema::hasTable('hotel_order_lines') && Schema::hasColumn('hotel_order_lines', 'turno_operativo_id') && $this->hasIndex('hotel_order_lines', 'hotel_order_lines_turno_idx')) {
            Schema::table('hotel_order_lines', function (Blueprint $table) {
                $table->dropIndex('hotel_order_lines_turno_idx');
                $table->dropColumn('turno_operativo_id');
            });
        }
    }

    protected function hasIndex($tableName, $indexName)
    {
        return count(DB::select(
            'SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1',
            array($tableName, $indexName)
        )) > 0;
    }
}
