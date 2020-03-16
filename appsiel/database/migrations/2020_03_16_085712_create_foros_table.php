<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateForosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_foros', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->text('contenido');
            $table->integer('periodo_id'); //sga_periodos_lectivo periodo escolar
            $table->unsignedInteger('user_id'); //autor
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->unsignedInteger('curso_id'); //grupo-curso
            $table->foreign('curso_id')->references('id')->on('sga_cursos')->onDelete('CASCADE');
            $table->unsignedInteger('asignatura_id'); //materia
            $table->foreign('asignatura_id')->references('id')->on('sga_asignaturas')->onDelete('CASCADE');
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
        Schema::drop('foros');
    }
}
