<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnContratoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->string('origen')->change();
            $table->string('destino')->change();
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
            $table->dropColumn('origen');
            $table->dropColumn('destino');
        });
    }
}
