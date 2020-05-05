<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TablaPwPqr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_pqr_form',function (Blueprint $table){
            $table->increments('id');
            $table->longtext('contenido_encabezado');
            $table->longtext('contenido_pie_formulario');
            $table->longtext('campos_mostrar');
            $table->longtext('parametros');
            $table->unsignedInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('pw_widget')->onDelete('CASCADE');
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
        Schema::drop('pw_pqr_form');
    }
}
