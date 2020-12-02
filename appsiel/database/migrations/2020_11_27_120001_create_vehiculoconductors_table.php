<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiculoconductorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_vehiculoconductors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('vehiculo_id'); //vehiculo
            $table->foreign('vehiculo_id')->references('id')->on('cte_vehiculos')->onDelete('CASCADE');
            $table->unsignedInteger('conductor_id'); //conductor
            $table->foreign('conductor_id')->references('id')->on('cte_conductors')->onDelete('CASCADE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cte_vehiculoconductors');
    }
}
