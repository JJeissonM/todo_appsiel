<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRegistroRetencionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contab_registros_retenciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('tipo');
            $table->string('numero_certificado');
            $table->date('fecha_certificado');
            $table->date('fecha_recepcion_certificado');
            $table->string('numero_doc_identidad_agente_retencion');
            $table->string('razon_social_agente_retencion');
            $table->integer('contab_retencion_id')->unsigned()->index();
            $table->double('valor_base_retencion');
            $table->double('tasa_retencion');
            $table->double('valor');
            $table->longtext('detalle');
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo')->unsigned()->index();
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('contab_registros_retenciones');
    }
}
