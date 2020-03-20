<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlantillasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_plantillas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('estado', 5)->default('NO'); // es actual: SI,  NO
            $table->string('titulo');
            $table->string('direccion');
            $table->string('telefono');
            $table->string('correo');
            $table->string('firma');
            $table->text('pie_pagina1'); //en formato json y guarda un arreglo de elementos para el pie
            $table->string('titulo_atras');
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
        Schema::drop('plantillas');
    }
}
