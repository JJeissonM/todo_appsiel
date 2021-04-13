<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmpleadoOrdenDeTrabajosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_empleados_ordenes_de_trabajo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('orden_trabajo_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->double('cantidad_horas');
            $table->double('valor_por_hora');
            $table->double('valor_devengo');
            $table->string('estado');
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
        Schema::drop('nom_empleados_ordenes_de_trabajo');
    }
}
