<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateModulosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_modulos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->longtext('detalle');
            $table->longtext('contenido');
            $table->longtext('parametros');
            $table->string('orden');
            $table->integer('seccion_id')->unsigned()->index();
            $table->string('mostrar_titulo');
            $table->string('tipo_modulo');
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
        Schema::drop('pw_modulos');
    }
}
