<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaFodaEstudiantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_foda_estudiantes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->unsigned()->index();
            $table->date('fecha_analisis');
            $table->string('tipo_caracteristica');
            $table->longtext('descripcion');
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
        Schema::drop('sga_foda_estudiantes');
    }
}
