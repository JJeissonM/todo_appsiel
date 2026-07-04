<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelStaysTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_stays', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('main_cliente_id')->unsigned();
            $table->integer('room_id')->unsigned();
            $table->dateTime('check_in_at');
            $table->dateTime('expected_check_out_at')->nullable();
            $table->dateTime('check_out_at')->nullable();
            $table->integer('adults_count')->default(1);
            $table->integer('children_count')->default(0);
            $table->integer('total_guests')->default(1);
            $table->string('status', 30)->default('ACTIVA');
            $table->text('notes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('closed_by')->unsigned()->nullable();
            $table->timestamps();

            $table->index(array('empresa_id', 'room_id', 'status'));
            $table->index(array('empresa_id', 'main_cliente_id'));
            $table->index(array('empresa_id', 'check_in_at'));
            $table->index(array('empresa_id', 'status'));
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_stays');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
