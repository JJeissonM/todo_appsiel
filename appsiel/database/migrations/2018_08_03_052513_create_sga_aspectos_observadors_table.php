<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaAspectosObservadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_aspectos_observador', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->unsigned()->index();
            $table->integer('id_aspecto')->unsigned()->index();
            $table->integer('id_periodo')->unsigned()->index();
            $table->date('fecha_valoracion');
            $table->string('valoracion_letras');
            $table->integer('valoracion_numeros');
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
        Schema::drop('sga_aspectos_observador');
    }
}
