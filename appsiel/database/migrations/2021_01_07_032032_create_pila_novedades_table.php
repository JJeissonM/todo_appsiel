<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaNovedadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_liquidacion_novedades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('planilla_generada_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->string('ing');
            $table->string('ret');
            $table->string('tde');
            $table->string('tae');
            $table->string('tdp');
            $table->string('tap');
            $table->string('vsp');
            $table->string('cor');
            $table->string('vst');
            $table->string('sln');
            $table->string('ige');
            $table->string('lma');
            $table->string('vac');
            $table->string('avp');
            $table->string('vct');
            $table->string('irp');
            $table->double('salario_basico');
            $table->string('tipo_de_salario');
            $table->integer('numero_de_horas_laboradas');
            $table->date('fecha_de_ingreso');
            $table->date('fecha_de_retiro');
            $table->date('fecha_inicial_variacion_permanente_de_salario_vsp');
            $table->date('fecha_inicial_suspension_temporal_del_contrato_sln');
            $table->date('fecha_final_suspension_temporal_del_contrato_sln');
            $table->date('fecha_inicial_incapacidad_enfermedad_general_ige');
            $table->date('fecha_final_incapacidad_enfermedad_general_ige');
            $table->date('fecha_inicial_licencia_por_maternidad_lma');
            $table->date('fecha_final_licencia_por_maternidad_lma');
            $table->date('fecha_inicial_vacaciones_licencias_remuneradas_vac');
            $table->date('fecha_final_vacaciones_licencias_remuneradas_vac');
            $table->date('fecha_inicial_variacion_centro_de_trabajo_vct');
            $table->date('fecha_final_variacion_centro_de_trabajo_vct');
            $table->date('fecha_inicial_incapacidad_riesgos_laborales_irp');
            $table->date('fecha_final_incapacidad_riesgos_laborales_irp');
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
        Schema::drop('nom_pila_liquidacion_novedades');
    }
}
