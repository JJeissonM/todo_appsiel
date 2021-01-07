<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaRiesgoLaboralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_liquidacion_riesgos_laborales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('planilla_generada_id')->unsigned()->index('planilla_generada_idw');
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->string('codigo_arl');
            $table->integer('dias_cotizados_riesgos_laborales');
            $table->double('ibc_riesgos_laborales');
            $table->double('tarifa_riesgos_laborales');
            $table->double('total_cotizacion_riesgos_laborales');
            $table->string('clase_de_riesgo');
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
        Schema::drop('nom_pila_liquidacion_riesgos_laborales');
    }
}
