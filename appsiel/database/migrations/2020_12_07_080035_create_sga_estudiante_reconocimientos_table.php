<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaEstudianteReconocimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_estudiante_reconocimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('estudiante_id');
            $table->unsignedInteger('curso_id');
            $table->unsignedInteger('periodo_lectivo_id');
            $table->string('descripcion');
            $table->text('resumen');
            $table->string('archivo_adjunto');
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
        Schema::drop('sga_estudiante_reconocimientos');
    }
}
