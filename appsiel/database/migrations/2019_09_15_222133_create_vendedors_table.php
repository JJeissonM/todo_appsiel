<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVendedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_vendedores', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('equipo_ventas_id')->unsigned()->index();
            $table->integer('clase_vendedor_id')->unsigned()->index();
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
        Schema::drop('vtas_vendedores');
    }
}
