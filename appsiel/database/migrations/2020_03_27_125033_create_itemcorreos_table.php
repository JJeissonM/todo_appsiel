<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemcorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_itemcorreos', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('activo', ['SI', 'NO']);
            $table->string('correo');
            $table->string('asunto');
            $table->string('encabezado');
            $table->text('contenido');
            $table->string('destinatario');
            $table->unsignedInteger('correo_id');
            $table->foreign('correo_id')->references('id')->on('pw_correos')->onDelete('cascade');
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
        Schema::drop('pw_itemcorreos');
    }
}
