<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddBotFieldsToComprasDocEncabezados extends Migration
{
    public function up()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->string('cufe', 100)->nullable()->after('doc_proveedor_consecutivo');
            $table->boolean('sincronizado_bot')->default(false)->after('cufe');
        });
    }
 
    public function down()
    {
        Schema::table('compras_doc_encabezados', function (Blueprint $table) {
            $table->dropColumn(['cufe', 'sincronizado_bot']);
        });
    }
}