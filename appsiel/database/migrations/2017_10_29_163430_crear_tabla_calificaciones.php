<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaCalificaciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('anio');
            $table->integer('id_periodo');
            $table->integer('id_grado');
            $table->integer('id_estudiante');
            $table->integer('id_asignatura');
            $table->float('calificacion');
            $table->string('logros',100);
            $table->integer('creado_por');
			$table->integer('modificado_por');
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
        Schema::drop('calificaciones');
    }
}
