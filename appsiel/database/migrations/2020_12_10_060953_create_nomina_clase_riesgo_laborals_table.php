<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaClaseRiesgoLaboralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_clases_riesgos_laborales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->longtext('detalle');
            $table->double('porcentaje_liquidacion');
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
        Schema::drop('nom_clases_riesgos_laborales');
    }
}
