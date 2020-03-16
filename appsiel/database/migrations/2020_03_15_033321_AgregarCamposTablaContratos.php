<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregarCamposTablaContratos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nom_contratos', function(Blueprint $table){
          $table->integer('liquida_subsidio_transporte');
          $table->integer('planilla_pila_id')->unsigned()->index();
          $table->boolean('es_pasante_sena');
          $table->integer('entidad_cesantias_id')->unsigned()->index();
          $table->integer('entidad_caja_compensacion_id')->unsigned()->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nom_contratos', function(Blueprint $table){
            $table->dropColumn('liquida_subsidio_transporte');
            $table->dropColumn('planilla_pila_id');
            $table->dropColumn('es_pasante_sena');
            $table->dropColumn('entidad_cesantias_id');
            $table->dropColumn('entidad_caja_compensacion_id');
        });
    }
}
