<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUpdateByToHotelStaysTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('hotel_stays') || Schema::hasColumn('hotel_stays', 'update_by')) {
            return;
        }

        Schema::table('hotel_stays', function (Blueprint $table) {
            $table->integer('update_by')->unsigned()->nullable()->after('closed_by');
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('hotel_stays') || !Schema::hasColumn('hotel_stays', 'update_by')) {
            return;
        }

        Schema::table('hotel_stays', function (Blueprint $table) {
            $table->dropColumn('update_by');
        });
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
