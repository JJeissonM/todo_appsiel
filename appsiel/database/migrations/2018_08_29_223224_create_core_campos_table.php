<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreCamposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_campos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo');
            $table->string('name');
            $table->string('etiqueta');
            $table->string('opciones');
            $table->string('value');
            $table->string('atributos');
            $table->string('icono');
            $table->string('html_clase');
            $table->string('html_id');
            $table->boolean('requerido');
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
        Schema::drop('sys_campos');
    }
}
