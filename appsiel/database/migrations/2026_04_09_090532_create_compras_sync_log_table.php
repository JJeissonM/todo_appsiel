<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateComprasSyncLogTable extends Migration
{
    public function up()
    {
        Schema::create('compras_sync_log', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cufe', 100);
            $table->unsignedInteger('core_empresa_id');
            $table->unsignedInteger('compras_doc_encabezado_id')->nullable();
            $table->string('estado', 20)->default('procesado'); // procesado | fallido | duplicado
            $table->text('mensaje_error')->nullable();
            $table->string('creado_por', 100)->nullable();
            $table->timestamps();

            $table->unique(['cufe', 'core_empresa_id']); // garantiza idempotencia
            $table->foreign('core_empresa_id')
                  ->references('id')->on('core_empresas');
            $table->foreign('compras_doc_encabezado_id')
                  ->references('id')->on('compras_doc_encabezados')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_sync_log');
    }
}