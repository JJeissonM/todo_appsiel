<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuscripcionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_suscripciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('cliente_id')->unsigned()->index();
            $table->date('fecha_desde');
            $table->date('fecha_hasta');
            $table->integer('plantilla_suscripcion_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('vtas_suscripciones');
    }
}
