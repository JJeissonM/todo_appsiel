<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModeloRelacionadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sys_modelos_relacionados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('modelo_principal_id')->unsigned()->index();
            $table->integer('modelo_relacionado_id')->unsigned()->index();
            $table->integer('orden');
            $table->string('tipo_modelo_relacionado');
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
        Schema::drop('sys_modelos_relacionados');
    }
}
