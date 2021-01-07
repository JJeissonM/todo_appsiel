<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaSaludsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_liquidacion_salud', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('planilla_generada_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->string('codigo_entidad_salud');
            $table->integer('dias_cotizados_salud');
            $table->double('ibc_salud');
            $table->double('tarifa_salud');
            $table->double('cotizacion_salud');
            $table->double('valor_upc_adicional_salud');
            $table->double('total_cotizacion_salud');
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
        Schema::drop('nom_pila_liquidacion_salud');
    }
}
