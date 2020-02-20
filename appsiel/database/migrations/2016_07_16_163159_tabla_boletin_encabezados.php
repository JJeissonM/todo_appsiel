<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TablaBoletinEncabezados extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boletin_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->index();
            $table->integer('id_grado');
            $table->integer('id_periodo');
            $table->integer('anio');
            $table->string('ciudad_colegio',100);
            $table->longText('observaciones');
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
        Schema::drop('boletin_encabezados');
    }
}
