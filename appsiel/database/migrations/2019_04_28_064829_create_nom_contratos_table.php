<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomContratosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_contratos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tercero_id')->unsigned()->index();
            $table->string('clase_contrato');
            $table->integer('cargo_id')->unsigned()->index();
            $table->integer('horas_laborales');
            $table->double('sueldo');
            $table->date('fecha_ingreso');
            $table->date('contrato_hasta');
            $table->integer('entidad_salud_id')->unsigned()->index();
            $table->integer('entidad_pension_id')->unsigned()->index();
            $table->integer('entidad_arl_id')->unsigned()->index();
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
        Schema::drop('nom_contratos');
    }
}
