<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEncabezadoCalificacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_calificaciones_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->string('columna_calificacion');
            $table->longtext('descripcion');
            $table->date('fecha');
            $table->integer('anio');
            $table->integer('periodo_id')->unsigned()->index();
            $table->integer('curso_id')->unsigned()->index();
            $table->integer('asignatura_id')->unsigned()->index();
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('sga_calificaciones_encabezados');
    }
}
