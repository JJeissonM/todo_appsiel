<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TablaBoletinContenidos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boletin_contenidos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('token_encabezado',100);
            $table->integer('id_asignatura');
            $table->float('calificacion');
            $table->string('logros',100);
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
        Schema::drop('boletin_contenidos');
    }
}
