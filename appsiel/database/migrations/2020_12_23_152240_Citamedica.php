<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Citamedica extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_citamedicas', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->string('estado');
            $table->unsignedInteger('consultorio_id');
            $table->foreign('consultorio_id')->references('id')->on('salud_consultorios')->onDelete('CASCADE');
            $table->unsignedInteger('profesional_id');
            $table->foreign('profesional_id')->references('id')->on('salud_profesionales')->onDelete('CASCADE');
            $table->unsignedInteger('paciente_id');
            $table->foreign('paciente_id')->references('id')->on('salud_pacientes')->onDelete('CASCADE');
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
        Schema::drop('salud_citamedicas');
    }
}
