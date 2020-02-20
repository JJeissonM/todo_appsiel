<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_clientes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('encabezado_dcto_pp_id')->unsigned()->index();
            $table->integer('clase_cliente_id')->unsigned()->index();
            $table->integer('lista_precios_id')->unsigned()->index();
            $table->integer('lista_descuentos_id')->unsigned()->index();
            $table->integer('zona_id')->unsigned()->index();
            $table->boolean('liquida_impuestos');
            $table->integer('condicion_pago_id')->unsigned()->index();
            $table->double('cupo_credito');
            $table->boolean('bloquea_por_cupo');
            $table->boolean('bloquea_por_mora');
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
        Schema::drop('vtas_clientes');
    }
}
