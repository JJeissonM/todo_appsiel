<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelRoomsTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->string('room_number', 20);
            $table->string('room_type', 30);
            $table->integer('inv_producto_id')->unsigned();
            $table->string('floor', 20)->nullable();
            $table->integer('capacity')->default(1);
            $table->string('status', 30)->default('DISPONIBLE');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique(array('empresa_id', 'room_number'));
            $table->index(array('empresa_id', 'status'));
            $table->index(array('empresa_id', 'room_type'));
            $table->index('inv_producto_id');
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_rooms');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
