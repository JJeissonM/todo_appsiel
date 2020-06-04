<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnsContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->string('rep_legal')->after('numero_contrato');
            $table->string('representacion_de')->after('rep_legal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->dropColumn('rep_legal');
            $table->dropColumn('representacion_de');
        });
    }
}
