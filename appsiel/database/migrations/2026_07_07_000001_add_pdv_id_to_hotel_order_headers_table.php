<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPdvIdToHotelOrderHeadersTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('hotel_order_headers')) {
            return;
        }

        if (!Schema::hasColumn('hotel_order_headers', 'pdv_id')) {
            Schema::table('hotel_order_headers', function (Blueprint $table) {
                $table->integer('pdv_id')->unsigned()->nullable()->after('cliente_id');
                $table->index('pdv_id');
            });
        }
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('hotel_order_headers')) {
            return;
        }

        if (Schema::hasColumn('hotel_order_headers', 'pdv_id')) {
            Schema::table('hotel_order_headers', function (Blueprint $table) {
                $table->dropIndex(array('pdv_id'));
                $table->dropColumn('pdv_id');
            });
        }
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
