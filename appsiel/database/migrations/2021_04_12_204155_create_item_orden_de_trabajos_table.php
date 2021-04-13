<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemOrdenDeTrabajosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_items_ordenes_de_trabajo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orden_trabajo_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->double('cantidad');
            $table->double('costo_unitario');
            $table->double('costo_total');
            $table->string('estado');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('nom_items_ordenes_de_trabajo');
    }
}
