<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipoVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_equipos_ventas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('descripcion')->unsigned()->index();
            $table->integer('equipo_padre_id')->unsigned()->index();
            $table->string('estado');
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
        Schema::drop('vtas_equipos_ventas');
    }
}
