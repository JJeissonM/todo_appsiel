<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpleadoPlanillasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_empleados_planilla', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orden');
            $table->integer('planilla_generada_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
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
        Schema::drop('nom_pila_empleados_planilla');
    }
}
