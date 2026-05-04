<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUmFactorToComprasPivotItemsXml extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('compras_pivot_items_xml')) {
            return;
        }

        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_pivot_items_xml', 'unidad_medida_local')) {
                $table->string('unidad_medida_local', 20)->nullable()->after('unidad_medida_xml');
            }

            if (!Schema::hasColumn('compras_pivot_items_xml', 'factor_conversion')) {
                $after = Schema::hasColumn('compras_pivot_items_xml', 'unidad_medida_local')
                    ? 'unidad_medida_local'
                    : 'unidad_medida_xml';
                $table->double('factor_conversion', 15, 6)->default(1)->after($after);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('compras_pivot_items_xml')) {
            return;
        }

        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (Schema::hasColumn('compras_pivot_items_xml', 'factor_conversion')) {
                $table->dropColumn('factor_conversion');
            }
            if (Schema::hasColumn('compras_pivot_items_xml', 'unidad_medida_local')) {
                $table->dropColumn('unidad_medida_local');
            }
        });
    }
}
