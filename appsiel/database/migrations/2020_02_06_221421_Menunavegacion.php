<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Menunavegacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_menunavegacion', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('descripcion');
            $table->string('icono');
            $table->string('enlace');
            $table->unsignedInteger('navegacion_id');
            $table->foreign('navegacion_id')->references('id')->on('pw_navegacion')->onDelete('CASCADE');
            $table->unsignedInteger('parent_id')->default('0');
            $table->enum('estado',['ACTIVO','INACTIVO']);
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
        Schema::drop('pw_menunavegacion');
    }
}
