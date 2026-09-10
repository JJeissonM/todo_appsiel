<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReteicaToComprasDocEncabezados extends Migration
{
    public function up()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->unsignedInteger('reteica_retencion_id')->default(0);
            $table->decimal('reteica_base', 15, 2)->default(0);
            $table->decimal('reteica_tasa', 8, 4)->default(0);
            $table->decimal('reteica_valor', 15, 2)->default(0);
        });
    }

    public function down()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->dropColumn(['reteica_retencion_id', 'reteica_base', 'reteica_tasa', 'reteica_valor']);
        });
    }
}
