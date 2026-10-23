<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReteicaBaseManualToCompras extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_doc_encabezados')) {
            return;
        }

        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->boolean('reteica_base_manual')->default(false);
        });
    }

    public function down()
    {
        if (!Schema::hasTable('compras_doc_encabezados')) {
            return;
        }

        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->dropColumn('reteica_base_manual');
        });
    }
}
