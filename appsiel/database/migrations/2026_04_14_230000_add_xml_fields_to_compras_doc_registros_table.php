<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXmlFieldsToComprasDocRegistrosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_doc_registros', 'xml_producto')) {
                $table->string('xml_producto')->nullable()->after('estado');
            }

            if (!Schema::hasColumn('compras_doc_registros', 'xml_codigo')) {
                $table->string('xml_codigo')->nullable()->after('xml_producto');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (Schema::hasColumn('compras_doc_registros', 'xml_codigo')) {
                $table->dropColumn('xml_codigo');
            }

            if (Schema::hasColumn('compras_doc_registros', 'xml_producto')) {
                $table->dropColumn('xml_producto');
            }
        });
    }
}
