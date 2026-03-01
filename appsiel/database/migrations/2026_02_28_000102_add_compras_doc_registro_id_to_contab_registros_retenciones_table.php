<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddComprasDocRegistroIdToContabRegistrosRetencionesTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('contab_registros_retenciones')) {
            return;
        }

        Schema::table('contab_registros_retenciones', function (Blueprint $table) {
            if (!Schema::hasColumn('contab_registros_retenciones', 'compras_doc_registro_id')) {
                $table->integer('compras_doc_registro_id')->unsigned()->default(0);
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('contab_registros_retenciones')) {
            return;
        }

        Schema::table('contab_registros_retenciones', function (Blueprint $table) {
            if (Schema::hasColumn('contab_registros_retenciones', 'compras_doc_registro_id')) {
                $table->dropColumn('compras_doc_registro_id');
            }
        });
    }
}
