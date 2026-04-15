<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddSyncFieldsToComprasPivotItemsXml extends Migration
{
    public function up()
    {
        $referenciaExiste = Schema::hasColumn('compras_pivot_items_xml', 'referencia');

        Schema::table('compras_pivot_items_xml', function (Blueprint $table) use ($referenciaExiste) {
            $table->unsignedInteger('compras_sync_log_id')->nullable()->after('proveedor_id');
            $table->string('unidad_medida_xml', 20)->nullable()->after('nombre_item_xml');

            if (!$referenciaExiste) {
                $table->string('referencia', 100)->nullable()->after('nombre_item_xml');
            }

            $table->foreign('compras_sync_log_id')
                  ->references('id')->on('compras_sync_log')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            $table->dropForeign(['compras_sync_log_id']);
            $table->dropColumn('compras_sync_log_id');
            $table->dropColumn('unidad_medida_xml');
        });

        // hasColumn fuera del closure también en down()
        if (Schema::hasColumn('compras_pivot_items_xml', 'referencia')) {
            Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
                $table->dropColumn('referencia');
            });
        }
    }
}