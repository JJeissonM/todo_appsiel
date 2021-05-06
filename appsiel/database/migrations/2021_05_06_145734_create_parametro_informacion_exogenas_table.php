<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParametroInformacionExogenasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_parametros_informacion_exogena', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->integer('tipo_informante')->unsigned()->index();
            $table->integer('grupo_salarios_id')->unsigned()->index();
            $table->integer('grupo_emolumentos_eclesiasticos_id')->unsigned()->index();
            $table->integer('grupo_honorarios_id')->unsigned()->index();
            $table->integer('grupo_servicios_id')->unsigned()->index();
            $table->integer('grupo_comisiones_id')->unsigned()->index();
            $table->integer('grupo_prestaciones_sociales_id')->unsigned()->index();
            $table->integer('grupo_viaticos_id')->unsigned()->index();
            $table->integer('grupo_gastos_representacion_id')->unsigned()->index();
            $table->integer('grupo_trabajo_cooperativo_id')->unsigned()->index();
            $table->integer('grupo_otros_pagos_id')->unsigned()->index();
            $table->integer('grupo_cesantias_e_intereses_pagadas_id')->unsigned()->index();
            $table->integer('grupo_pensiones_jubilacion_id')->unsigned()->index();
            $table->integer('grupo_aportes_salud_obligatoria_id')->unsigned()->index();
            $table->integer('grupo_aportes_pension_obligatoria_y_fsp_id')->unsigned()->index();
            $table->integer('grupo_aportes_voluntarios_pension_id')->unsigned()->index();
            $table->integer('grupo_aportes_afc_id')->unsigned()->index();
            $table->integer('grupo_aportes_avc_id')->unsigned()->index();
            $table->integer('grupo_valores_retefuente_id')->unsigned()->index();
            $table->integer('grupo_bonos_id')->unsigned()->index();
            $table->integer('grupo_recursos_publicos_para_educacion_id')->unsigned()->index();
            $table->integer('grupo_alimentacion_mayores_41uvt_id')->unsigned()->index();
            $table->integer('grupo_alimentacion_hasta_41uvt_id')->unsigned()->index();
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
        Schema::drop('nom_parametros_informacion_exogena');
    }
}
