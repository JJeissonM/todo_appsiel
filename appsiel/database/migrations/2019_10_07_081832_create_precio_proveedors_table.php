<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePrecioProveedorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_precios_proveedores', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('proveedor_id')->unsigned()->index();
            $table->integer('producto_id')->unsigned()->index();
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
        Schema::drop('compras_precios_proveedores');
    }
}
