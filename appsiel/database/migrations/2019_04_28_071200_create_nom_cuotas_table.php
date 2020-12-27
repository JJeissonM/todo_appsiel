<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomCuotasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_cuotas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->date('fecha_inicio');
            $table->double('valor_cuota');
            $table->double('tope_maximo');
            $table->double('valor_acumulado');
            $table->string('estado');
            $table->longtext('detalle');
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
        Schema::drop('nom_cuotas');
    }
}
