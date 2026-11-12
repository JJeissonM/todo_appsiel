<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddCostosIncluyenIvaToInvDocEncabezados extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('inv_doc_encabezados') || Schema::hasColumn('inv_doc_encabezados', 'costos_incluyen_iva')) {
            return;
        }
        Schema::table('inv_doc_encabezados', function (Blueprint $table) {
            $table->boolean('costos_incluyen_iva')->nullable();
        });
        // Las entradas ya facturadas usan costos de inventario sin IVA.
        // NULL permite interpretar las entradas manuales pendientes como precio final.
        DB::table('inv_doc_encabezados')->where('core_tipo_transaccion_id', 35)->where('estado', 'Facturada')
            ->update(['costos_incluyen_iva'=>0]);
    }

    public function down()
    {
        if (Schema::hasTable('inv_doc_encabezados') && Schema::hasColumn('inv_doc_encabezados', 'costos_incluyen_iva')) {
            Schema::table('inv_doc_encabezados', function (Blueprint $table) {
                $table->dropColumn('costos_incluyen_iva');
            });
        }
    }
}
