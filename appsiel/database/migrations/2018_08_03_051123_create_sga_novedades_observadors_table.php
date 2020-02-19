<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaNovedadesObservadorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_novedades_observador', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_estudiante')->unsigned()->index();
            $table->integer('id_periodo')->unsigned()->index();
            $table->date('fecha_novedad');
            $table->string('tipo_novedad');
            $table->longtext('descripcion');
            $table->integer('creada_por');
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
        Schema::drop('sga_novedades_observador');
    }
}
