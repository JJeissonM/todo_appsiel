<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultadoExamenMedicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_resultados_examenes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('paciente_id')->unsigned()->index();
            $table->integer('consulta_id')->unsigned()->index();
            $table->integer('examen_id')->unsigned()->index();
            $table->integer('variable_id')->unsigned()->index();
            $table->integer('organo_del_cuerpo_id')->unsigned()->index();
            $table->string('valor_resultado');
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
        Schema::drop('salud_resultados_examenes');
    }
}
