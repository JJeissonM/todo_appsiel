<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEscalaValoracionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escalas_valoracion', function (Blueprint $table) {
            $table->increments('id');
            $table->double('calificacion_minima');
            $table->double('calificacion_maxima');
            $table->string('nombre_escala');
            $table->string('sigla');
            $table->string('escala_nacional');
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
        Schema::drop('escalas_valoracion');
    }
}
