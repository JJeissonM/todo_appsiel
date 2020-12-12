<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNivelacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_notas_nivelaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('matricula_id')->unsigned()->index();
            $table->integer('colegio_id')->unsigned()->index();
            $table->integer('periodo_id')->unsigned()->index();
            $table->integer('curso_id')->unsigned()->index();
            $table->integer('asignatura_id')->unsigned()->index();
            $table->integer('estudiante_id')->unsigned()->index();
            $table->double('calificacion');
            $table->string('observacion');
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
        Schema::drop('sga_notas_nivelaciones');
    }
}
