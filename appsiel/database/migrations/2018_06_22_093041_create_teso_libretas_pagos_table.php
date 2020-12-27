<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesoLibretasPagosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teso_libretas_pagos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->unsigned()->index();
            $table->double('valor_anual');
            $table->date('fecha_inicio');
            $table->integer('numero_periodos');
            $table->double('valor_mensual');
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
        Schema::drop('teso_libretas_pagos');
    }
}
