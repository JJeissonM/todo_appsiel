<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDifoSeccionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('difo_secciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->string('presentacion');
            $table->integer('cantidad_filas');
            $table->integer('cantidad_columnas');
            $table->longText('contenido');
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
        Schema::drop('difo_seccions');
    }
}
