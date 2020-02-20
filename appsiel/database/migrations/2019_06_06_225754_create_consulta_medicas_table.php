<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConsultaMedicasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_consultas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('paciente_id')->unsigned()->index();
            $table->string('tipo_consulta');
            $table->date('fecha_consulta');
            $table->integer('profesional_salud_id')->unsigned()->index();
            $table->integer('consultorio_id')->unsigned()->index();
            $table->string('nombre_acompañante');
            $table->string('parentezco_acompañante');
            $table->string('sintomas');
            $table->longtext('diagnostico');
            $table->longtext('indicaciones');
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
        Schema::drop('salud_consultas');
    }
}
