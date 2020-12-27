<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePdvsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_pos_puntos_de_ventas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('descripcion')->unsigned()->index();
            $table->integer('bodega_default_id')->unsigned()->index();
            $table->integer('caja_default_id')->unsigned()->index();
            $table->integer('cajero_default_id')->unsigned()->index();
            $table->integer('cliente_default_id')->unsigned()->index();
            $table->integer('tipo_doc_app_default_id')->unsigned()->index();
            $table->longtext('detalle');
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
        Schema::drop('vtas_pos_puntos_de_ventas');
    }
}
