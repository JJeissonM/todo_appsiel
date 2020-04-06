<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDescuentoCompras extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('compras_doc_registros', function(Blueprint $table){
          $table->double('valor_total_descuento')->after('valor_impuesto');
          $table->double('tasa_descuento')->after('valor_impuesto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('compras_doc_registros', function(Blueprint $table){
            $table->dropColumn('tasa_descuento');
            $table->dropColumn('valor_total_descuento');
        });
    }
}
