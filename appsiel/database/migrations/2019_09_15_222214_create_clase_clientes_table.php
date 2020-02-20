<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaseClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_clases_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('cta_x_cobrar_id')->unsigned()->index();
            $table->integer('cta_anticipo_id')->unsigned()->index();
            $table->integer('clase_padre_id')->unsigned()->index();
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
        Schema::drop('vtas_clases_clientes');
    }
}
