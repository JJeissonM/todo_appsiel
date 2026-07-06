<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddHotelGuestEavIndexes extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('core_eav_valores')) {
            return;
        }

        if (!$this->indexExists('core_eav_valores', 'core_eav_hotel_guest_lookup_idx')) {
            Schema::table('core_eav_valores', function (Blueprint $table) {
                $table->index(array('modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'core_campo_id'), 'core_eav_hotel_guest_lookup_idx');
            });
        }
    }

    public function down()
    {
        if (!Schema::hasTable('core_eav_valores')) {
            return;
        }

        if ($this->indexExists('core_eav_valores', 'core_eav_hotel_guest_lookup_idx')) {
            Schema::table('core_eav_valores', function (Blueprint $table) {
                $table->dropIndex('core_eav_hotel_guest_lookup_idx');
            });
        }
    }

    private function hotelModuleEnabled()
    {
        $value = env('HOTEL_MODULE_ENABLED', false);

        return $value === true || $value === 1 || $value === '1' || strtolower((string)$value) === 'true' || strtolower((string)$value) === 'yes';
    }

    private function indexExists($table, $index)
    {
        return DB::table('information_schema.statistics')
            ->whereRaw('table_schema = DATABASE()')
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count() > 0;
    }
}
