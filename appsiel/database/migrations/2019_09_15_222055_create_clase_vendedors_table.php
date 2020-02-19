<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaseVendedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_clases_vendedores', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('descripcion')->unsigned()->index();
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
        Schema::drop('vtas_clases_vendedores');
    }
}
