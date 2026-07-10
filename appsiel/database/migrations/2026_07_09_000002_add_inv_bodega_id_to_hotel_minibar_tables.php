<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvBodegaIdToHotelMinibarTables extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        if (Schema::hasTable('hotel_rooms') && !Schema::hasColumn('hotel_rooms', 'inv_bodega_id')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->integer('inv_bodega_id')->unsigned()->nullable()->after('inv_producto_id');
                $table->index('inv_bodega_id');
            });
        }

        if (Schema::hasTable('hotel_order_lines') && !Schema::hasColumn('hotel_order_lines', 'inv_bodega_id')) {
            Schema::table('hotel_order_lines', function (Blueprint $table) {
                $table->integer('inv_bodega_id')->unsigned()->nullable()->after('room_id');
                $table->index('inv_bodega_id');
            });
        }
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        if (Schema::hasTable('hotel_order_lines') && Schema::hasColumn('hotel_order_lines', 'inv_bodega_id')) {
            Schema::table('hotel_order_lines', function (Blueprint $table) {
                $table->dropIndex(array('inv_bodega_id'));
                $table->dropColumn('inv_bodega_id');
            });
        }

        if (Schema::hasTable('hotel_rooms') && Schema::hasColumn('hotel_rooms', 'inv_bodega_id')) {
            Schema::table('hotel_rooms', function (Blueprint $table) {
                $table->dropIndex(array('inv_bodega_id'));
                $table->dropColumn('inv_bodega_id');
            });
        }
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
