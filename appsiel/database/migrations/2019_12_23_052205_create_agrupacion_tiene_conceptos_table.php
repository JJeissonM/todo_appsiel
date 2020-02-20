<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAgrupacionTieneConceptosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_agrupacion_tiene_conceptos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_agrupacion_id')->unsigned()->index();
            $table->string('nom_concepto_id');
            $table->string('orden');
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
        Schema::drop('nom_agrupacion_tiene_conceptos');
    }
}
