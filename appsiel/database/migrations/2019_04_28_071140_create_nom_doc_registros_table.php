<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomDocRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_doc_encabezado_id')->unsigned()->index();
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('codigo_referencia_tercero');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->string('detalle');
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->integer('cantidad_horas');
            $table->integer('porcentaje');
            $table->double('valor_devengo');
            $table->double('valor_deduccion');
            $table->string('estado');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('nom_doc_registros');
    }
}
