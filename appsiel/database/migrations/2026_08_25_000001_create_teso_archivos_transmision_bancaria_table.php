<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTesoArchivosTransmisionBancariaTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('teso_archivos_transmision_bancaria')) {
            return;
        }

        Schema::create('teso_archivos_transmision_bancaria', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('core_empresa_id');
            $table->unsignedInteger('teso_doc_encabezado_id');
            $table->string('formato', 40);
            $table->string('nombre_archivo', 160);
            $table->string('hash_sha256', 64);
            $table->unsignedInteger('cantidad_registros');
            $table->unsignedInteger('cantidad_omitidos')->default(0);
            $table->decimal('valor_total', 20, 2);
            $table->string('generado_por');
            $table->timestamps();

            $table->index(['core_empresa_id', 'teso_doc_encabezado_id'], 'teso_archivo_banco_pago_idx');
            $table->index('hash_sha256', 'teso_archivo_banco_hash_idx');
        });
    }

    public function down()
    {
        Schema::dropIfExists('teso_archivos_transmision_bancaria');
    }
}
