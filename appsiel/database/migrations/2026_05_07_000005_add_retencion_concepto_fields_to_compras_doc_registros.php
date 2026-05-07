<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddRetencionConceptoFieldsToComprasDocRegistros extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_doc_registros', 'retencion_fuente_concepto_anual_id')) {
                $table->unsignedInteger('retencion_fuente_concepto_anual_id')->default(0);
            }

            if (!Schema::hasColumn('compras_doc_registros', 'retencion_fuente_codigo')) {
                $table->string('retencion_fuente_codigo', 80)->default('');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (Schema::hasColumn('compras_doc_registros', 'retencion_fuente_codigo')) {
                $table->dropColumn('retencion_fuente_codigo');
            }

            if (Schema::hasColumn('compras_doc_registros', 'retencion_fuente_concepto_anual_id')) {
                $table->dropColumn('retencion_fuente_concepto_anual_id');
            }
        });
    }
}
