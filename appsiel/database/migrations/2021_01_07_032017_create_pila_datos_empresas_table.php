<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaDatosEmpresasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_datos_empresa', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->string('tipo_aportante');
            $table->string('clase_aportante');
            $table->string('forma_presentacion');
            $table->string('tipo_persona');
            $table->string('naturaleza_juridica');
            $table->string('tipo_pagador_pensiones');
            $table->string('tipo_accion');
            $table->integer('administradora_riesgos_laborales_id')->unsigned()->index();
            $table->string('actividad_economica_ciiu');
            $table->integer('rep_legal_core_tercero_id')->unsigned()->index();
            $table->double('porcentaje_sena');
            $table->double('porcentaje_icbf');
            $table->double('porcentaje_caja_compensacion');
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
        Schema::drop('nom_pila_datos_empresa');
    }
}
