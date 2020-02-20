<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListaDctoDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_listas_dctos_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lista_descuentos_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->date('fecha_activacion');
            $table->double('descuento1');
            $table->double('descuento2');
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
        Schema::drop('vtas_listas_dctos_detalles');
    }
}
