<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateInvDocumentosRelacionadosTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('inv_documentos_relacionados')) {
            return;
        }

        Schema::create('inv_documentos_relacionados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inv_doc_encabezado_origen_id');
            $table->unsignedInteger('inv_doc_encabezado_relacionado_id');
            $table->string('tipo_relacion', 100);
            $table->string('creado_por', 100);
            $table->string('modificado_por', 100)->nullable();
            $table->timestamps();

            $table->index('inv_doc_encabezado_origen_id', 'idx_inv_doc_rel_origen');
            $table->index('inv_doc_encabezado_relacionado_id', 'idx_inv_doc_rel_relacionado');
            $table->unique(
                ['inv_doc_encabezado_origen_id', 'inv_doc_encabezado_relacionado_id', 'tipo_relacion'],
                'uq_inv_doc_rel_origen_relacionado_tipo'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('inv_documentos_relacionados');
    }
}
