<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePacientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_pacientes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->string('codigo_historia_clinica');
            $table->date('fecha_nacimiento');
            $table->string('genero');
            $table->string('ocupacion');
            $table->string('estado_civil');
            $table->string('grupo_sanguineo');
            $table->string('remitido_por');
            $table->string('nivel_academico');
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
        Schema::drop('salud_pacientes');
    }
}
