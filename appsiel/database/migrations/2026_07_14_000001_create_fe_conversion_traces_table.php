<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFeConversionTracesTable extends Migration
{
    public function up()
    {
        Schema::create('fe_conversion_traces', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->nullable();
            $table->integer('vtas_doc_encabezado_id')->unsigned();
            $table->integer('origen_core_tipo_transaccion_id')->unsigned();
            $table->integer('origen_core_tipo_doc_app_id')->unsigned();
            $table->integer('origen_consecutivo')->unsigned();
            $table->integer('destino_core_tipo_transaccion_id')->unsigned()->nullable();
            $table->integer('destino_core_tipo_doc_app_id')->unsigned()->nullable();
            $table->integer('destino_consecutivo')->unsigned()->nullable();
            $table->string('estado', 30);
            $table->string('referencia', 100)->nullable();
            $table->text('motivo')->nullable();
            $table->text('metadata')->nullable();
            $table->string('creado_por')->nullable();
            $table->string('modificado_por')->nullable();
            $table->timestamps();

            $table->index('vtas_doc_encabezado_id', 'fe_conv_doc_idx');
            $table->index('estado', 'fe_conv_estado_idx');
            $table->index(
                ['core_empresa_id', 'origen_core_tipo_transaccion_id', 'origen_core_tipo_doc_app_id', 'origen_consecutivo'],
                'fe_conv_origen_idx'
            );
            $table->index(
                ['core_empresa_id', 'destino_core_tipo_transaccion_id', 'destino_core_tipo_doc_app_id', 'destino_consecutivo'],
                'fe_conv_destino_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('fe_conversion_traces');
    }
}
