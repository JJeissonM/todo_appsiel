<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClaseProveedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_clases_proveedores', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('cta_x_pagar_id')->unsigned()->index();
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
        Schema::drop('compras_clases_proveedores');
    }
}
