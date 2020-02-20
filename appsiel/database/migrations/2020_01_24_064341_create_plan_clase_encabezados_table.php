<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanClaseEncabezadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_plan_clases_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plantilla_plan_clases_id')->unsigned()->index();
            $table->date('fecha');
            $table->integer('semana_calendario_id')->unsigned()->index();
            $table->integer('periodo_id')->unsigned()->index();
            $table->integer('curso_id')->unsigned()->index();
            $table->integer('asignatura_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
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
        Schema::drop('sga_plan_clases_encabezados');
    }
}
