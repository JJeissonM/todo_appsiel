<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesoPlanPagosEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teso_cartera_estudiantes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->unsigned()->index();
            $table->string('concepto');
            $table->double('valor_cartera');
            $table->date('fecha_vencimiento');
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
        Schema::drop('teso_cartera_estudiantes');
    }
}
