<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RefactorPivotItemsXmlRemoveUmLocalAddTipoFactor extends Migration
{
    /**
     * Elimina unidad_medida_local (ya no se usa; la U.M. viene del producto Appsiel)
     * y agrega tipo_factor (multiplicacion|division) para la conversión de cantidades.
     */
    public function up()
    {
        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (Schema::hasColumn('compras_pivot_items_xml', 'unidad_medida_local')) {
                $table->dropColumn('unidad_medida_local');
            }
        });

        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_pivot_items_xml', 'tipo_factor')) {
                $table->string('tipo_factor', 20)->default('division')->after('factor_conversion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (Schema::hasColumn('compras_pivot_items_xml', 'tipo_factor')) {
                $table->dropColumn('tipo_factor');
            }
        });

        Schema::table('compras_pivot_items_xml', function (Blueprint $table) {
            if (!Schema::hasColumn('compras_pivot_items_xml', 'unidad_medida_local')) {
                $table->string('unidad_medida_local', 20)->nullable()->after('unidad_medida_xml');
            }
        });
    }
}
