<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeInvProductoIdNullableOnComprasPivotItemsXml extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_pivot_items_xml') || !Schema::hasColumn('compras_pivot_items_xml', 'inv_producto_id')) {
            return;
        }

        DB::statement('ALTER TABLE compras_pivot_items_xml MODIFY inv_producto_id INT(10) UNSIGNED NULL');
    }

    public function down()
    {
        if (!Schema::hasTable('compras_pivot_items_xml') || !Schema::hasColumn('compras_pivot_items_xml', 'inv_producto_id')) {
            return;
        }

        DB::statement('ALTER TABLE compras_pivot_items_xml MODIFY inv_producto_id INT(10) UNSIGNED NOT NULL');
    }
}
