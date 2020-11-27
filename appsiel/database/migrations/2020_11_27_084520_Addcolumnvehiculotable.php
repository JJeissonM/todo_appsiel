<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnvehiculotable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_vehiculos', function (Blueprint $table) {
            $table->string('bloqueado_cuatro_contratos')->default('NO')->after('int');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_vehiculos', function (Blueprint $table) {
            $table->dropColumn('bloqueado_cuatro_contratos');
        });
    }
}
