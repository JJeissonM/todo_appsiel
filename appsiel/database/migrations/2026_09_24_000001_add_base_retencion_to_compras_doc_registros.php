<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBaseRetencionToComprasDocRegistros extends Migration
{
    public function up()
    {
        Schema::table('compras_doc_registros', function (Blueprint $table) {
            // NULL identifica documentos anteriores a la base editable.
            $table->decimal('base_retencion', 15, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::table('compras_doc_registros', function (Blueprint $table) {
            $table->dropColumn('base_retencion');
        });
    }
}
