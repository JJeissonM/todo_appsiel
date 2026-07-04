<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelReservationsTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('cliente_id')->unsigned();
            $table->integer('room_id')->unsigned();
            $table->date('reserved_from');
            $table->date('reserved_until');
            $table->string('status', 30)->default('ACTIVA');
            $table->text('notes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('fulfilled_stay_id')->unsigned()->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamps();

            $table->index(array('empresa_id', 'room_id', 'status'));
            $table->index(array('empresa_id', 'cliente_id'));
            $table->index(array('empresa_id', 'reserved_from'));
            $table->index(array('empresa_id', 'reserved_until'));
            $table->index(array('empresa_id', 'status'));
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_reservations');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
