<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddRetencionFieldsToComprasDocRegistrosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_doc_registros', 'contab_retencion_id')) {
                $table->integer('contab_retencion_id')->unsigned()->default(0);
            }

            if (!Schema::hasColumn('compras_doc_registros', 'tasa_retencion')) {
                $table->float('tasa_retencion')->default(0);
            }

            if (!Schema::hasColumn('compras_doc_registros', 'valor_retencion')) {
                $table->double('valor_retencion', 15, 2)->default(0);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (Schema::hasColumn('compras_doc_registros', 'valor_retencion')) {
                $table->dropColumn('valor_retencion');
            }

            if (Schema::hasColumn('compras_doc_registros', 'tasa_retencion')) {
                $table->dropColumn('tasa_retencion');
            }

            if (Schema::hasColumn('compras_doc_registros', 'contab_retencion_id')) {
                $table->dropColumn('contab_retencion_id');
            }
        });
    }
}
