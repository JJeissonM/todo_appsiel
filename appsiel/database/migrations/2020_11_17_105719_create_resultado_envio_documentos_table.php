<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultadoEnvioDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fe_resultados_envios_documentos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vtas_doc_encabezado_id')->unsigned();
            $table->integer('codigo');
            $table->string('consecutivoDocumento');
            $table->longText('cufe');
            $table->boolean('esValidoDian');
            $table->dateTime('fechaAceptacionDIAN');
            $table->dateTime('fechaRespuesta');
            $table->longText('hash');
            $table->longText('mensaje');
            $table->longText('mensajesValidacion');
            $table->longText('nombre');
            $table->longText('qr');
            $table->longText('reglasNotificacionDIAN');
            $table->longText('reglasValidacionDIAN');
            $table->string('resultado');
            $table->string('tipoCufe');
            $table->longText('xml');
            $table->longText('tipoDocumento');
            $table->longText('trackID');
            $table->boolean('poseeAdjuntos');
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
        Schema::drop('fe_resultados_envios_documentos');
    }
}
