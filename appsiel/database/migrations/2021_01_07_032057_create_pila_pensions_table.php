<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaPensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_liquidacion_pension', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('planilla_generada_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->string('codigo_entidad_pension');
            $table->integer('dias_cotizados_pension');
            $table->double('ibc_pension');
            $table->double('tarifa_pension');
            $table->double('cotizacion_pension');
            $table->double('afp_voluntario_rais_empleado');
            $table->double('afp_voluntatio_rais_empresa');
            $table->double('subcuenta_solidaridad_fsp');
            $table->double('subcuenta_subsistencia_fsp');
            $table->double('total_cotizacion_pension');
            $table->double('valor_cotizacion_pension');
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
        Schema::drop('nom_pila_liquidacion_pension');
    }
}
