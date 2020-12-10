<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNominaNovedadTnlsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_novedades_tnl', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_inicial_tnl');
            $table->date('fecha_final_tnl');
            $table->integer('cantidad_dias_tnl');
            $table->integer('cantidad_horas_tnl');
            $table->string('tipo_novedad_tnl');
            $table->string('codigo_diagnostico_incapacidad');
            $table->string('numero_incapacidad');
            $table->date('fecha_expedicion_incapacidad');
            $table->string('origen_incapacidad');
            $table->string('clase_incapacidad');
            $table->date('fecha_incapacidad');
            $table->double('valor_a_pagar_eps');
            $table->double('valor_a_pagar_arl');
            $table->double('valor_a_pagar_empresa');
            $table->longtext('observaciones');
            $table->string('estado');
            $table->integer('cantidad_dias_amortizados');
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
        Schema::drop('nom_novedades_tnl');
    }
}
