<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddXmlCantidadYPrecioUnitarioToComprasDocRegistrosTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_doc_registros', 'xml_cantidad')) {
                $table->double('xml_cantidad', 15, 6)->nullable()->after('xml_codigo');
            }

            if (!Schema::hasColumn('compras_doc_registros', 'xml_precio_unitario')) {
                $table->double('xml_precio_unitario', 15, 6)->nullable()->after('xml_cantidad');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_registros')) {
            return;
        }

        Schema::table('compras_doc_registros', function (Blueprint $table) {
            if (Schema::hasColumn('compras_doc_registros', 'xml_precio_unitario')) {
                $table->dropColumn('xml_precio_unitario');
            }

            if (Schema::hasColumn('compras_doc_registros', 'xml_cantidad')) {
                $table->dropColumn('xml_cantidad');
            }
        });
    }
}

