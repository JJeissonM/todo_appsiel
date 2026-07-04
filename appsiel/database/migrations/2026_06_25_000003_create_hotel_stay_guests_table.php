<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHotelStayGuestsTable extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::create('hotel_stay_guests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('empresa_id')->unsigned();
            $table->integer('stay_id')->unsigned();
            $table->integer('cliente_id')->unsigned();
            $table->boolean('is_main_guest')->default(0);
            $table->string('relationship', 50)->nullable();
            $table->timestamps();

            $table->unique(array('empresa_id', 'stay_id', 'cliente_id'));
            $table->index(array('empresa_id', 'stay_id'));
            $table->index(array('empresa_id', 'cliente_id'));
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        Schema::drop('hotel_stay_guests');
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
