<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreinformeAcademicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_preinformes_academicos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo_matricula');
            $table->integer('id_colegio')->unsigned()->index();
            $table->integer('anio');
            $table->integer('id_periodo')->unsigned()->index();
            $table->integer('curso_id')->unsigned()->index();
            $table->integer('id_estudiante')->unsigned()->index();
            $table->integer('id_asignatura')->unsigned()->index();
            $table->longtext('anotacion');
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
        Schema::drop('sga_preinformes_academicos');
    }
}
