<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomCargosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_cargos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->string('estado');
            $table->integer('cargo_padre_id')->unsigned()->index();
            $table->integer('rango_salarial_id')->unsigned()->index();
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
        Schema::drop('nom_cargos');
    }
}
