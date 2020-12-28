<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametroLiquidacionPrestacionesSocialesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_parametros_liquidacion_prestaciones_sociales', function (Blueprint $table) {
            $table->increments('id');
            $table->string('concepto_prestacion');
            $table->integer('grupo_empleado_id')->unsigned()->index('grupo_empleado_id_index');
            $table->integer('nom_agrupacion_id')->unsigned()->index('nom_agrupacion_id_index');
            $table->integer('nom_agrupacion2_id')->unsigned()->index('nom_agrupacion2_id_index');
            $table->string('base_liquidacion');
            $table->integer('cantidad_meses_a_promediar');
            $table->integer('dias_a_liquidar');
            $table->integer('sabado_es_dia_habil');
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
        Schema::drop('nom_parametros_liquidacion_prestaciones_sociales');
    }
}
