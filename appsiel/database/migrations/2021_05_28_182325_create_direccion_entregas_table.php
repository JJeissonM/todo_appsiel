<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDireccionEntregasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_direcciones_entrega_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned()->index();
            $table->string('nombre_contacto');
            $table->integer('codigo_ciudad');
            $table->string('direccion1');
            $table->string('barrio');
            $table->string('codigo_postal');
            $table->string('telefono1');
            $table->string('datos_adicionales');
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
        Schema::drop('vtas_direcciones_entrega_clientes');
    }
}
