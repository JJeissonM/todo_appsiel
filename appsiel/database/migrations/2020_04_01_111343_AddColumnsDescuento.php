<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsDescuento extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vtas_doc_registros', function(Blueprint $table){
          $table->double('valor_total_descuento')->after('base_impuesto_total');
          $table->double('tasa_descuento')->after('base_impuesto_total');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vtas_doc_registros', function(Blueprint $table){
            $table->dropColumn('tasa_descuento');
            $table->dropColumn('valor_total_descuento');
        });
    }
}
