<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProgramacionAulaVirtualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_programacion_aula_virtual', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('curso_id')->unsigned()->index();
            $table->string('descripcion');
            $table->string('tipo_evento');
            $table->string('dia_semana');
            $table->time('hora_inicio');
            $table->date('fecha');
            $table->integer('asignatura_id')->unsigned()->index();
            $table->integer('guia_academica_id')->unsigned()->index();
            $table->integer('actividad_escolar_id')->unsigned()->index();
            $table->longtext('enlace_reunion_virtual');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('sga_programacion_aula_virtual');
    }
}
