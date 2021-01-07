<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePilaParafiscalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_pila_liquidacion_parafiscales', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('planilla_generada_id')->unsigned()->index();
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->date('fecha_final_mes');
            $table->string('cotizante_exonerado_de_aportes_parafiscales');
            $table->string('codigo_entidad_ccf');
            $table->integer('dias_cotizados');
            $table->double('ibc_parafiscales');
            $table->double('tarifa_ccf');
            $table->double('cotizacion_ccf');
            $table->double('tarifa_sena');
            $table->double('cotizacion_sena');
            $table->double('tarifa_icbf');
            $table->double('cotizacion_icbf');
            $table->double('total_cotizacion');
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
        Schema::drop('nom_pila_liquidacion_parafiscales');
    }
}
