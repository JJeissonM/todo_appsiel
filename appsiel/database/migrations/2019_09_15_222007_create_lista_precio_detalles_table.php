<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListaPrecioDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_listas_precios_detalles', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lista_precios_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->date('fecha_activacion');
            $table->double('precio');
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
        Schema::drop('vtas_listas_precios_detalles');
    }
}
