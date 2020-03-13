<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRenameColumnPreguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_preguntas', function (Blueprint $table) {
            $table->renameColumn('pregunta','titulo');
            $table->renameColumn('respuesta','descripcion');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_preguntas', function (Blueprint $table) {
            $table->renameColumn('titulo','pregunta');
            $table->renameColumn('descripcion','respuesta');
        });
    }
}
