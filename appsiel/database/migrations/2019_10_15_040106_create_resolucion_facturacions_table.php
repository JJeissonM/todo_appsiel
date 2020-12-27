<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResolucionFacturacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_resoluciones_facturacion2', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('sucursal_id')->unsigned()->index();
            $table->integer('tipo_doc_app_id')->unsigned()->index();
            $table->string('numero_resolucion');
            $table->integer('numero_fact_inicial');
            $table->integer('numero_fact_final');
            $table->date('fecha_expedicion');
            $table->date('fecha_expiracion');
            $table->string('modalidad');
            $table->string('prefijo');
            $table->string('tipo_solicitud');
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
        Schema::drop('vtas_resoluciones_facturacion2');
    }
}
