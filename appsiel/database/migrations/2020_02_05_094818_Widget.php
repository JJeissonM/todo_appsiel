<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Widget extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_widget',function(Blueprint $table){
            $table->increments('id');
            $table->integer('orden');
            $table->enum('estado',['ACTIVO','INACTIVO']);
            $table->unsignedInteger('pagina_id');
            $table->unsignedInteger('seccion_id');
            $table->foreign('pagina_id')->references('id')->on('pw_paginas');
            $table->foreign('seccion_id')->references('id')->on('pw_seccion');
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
        Schema::drop('pw_widget');
    }
}
