<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_secciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('mostrar_titulo');
            $table->integer('cantidad_columnas');
            $table->longtext('detalle');
            $table->string('ancho_columnas');
            $table->longtext('elementos');
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
        Schema::drop('pw_secciones');
    }
}
