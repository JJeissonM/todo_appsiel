<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehiculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_vehiculos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('int', 10); //Número interno del movil
            $table->string('placa', 50);
            $table->string('numero_vin');
            $table->string('numero_motor');
            $table->string('modelo', 5);
            $table->string('marca', 100);
            $table->string('clase', 100); //tipo
            $table->string('color');
            $table->integer('cilindraje');
            $table->integer('capacidad');
            $table->date('fecha_control_kilometraje')->nullable();
            $table->unsignedInteger('propietario_id'); //dueño del vehiculo
            $table->foreign('propietario_id')->references('id')->on('cte_propietarios')->onDelete('CASCADE');
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
        Schema::drop('vehiculos');
    }
}
