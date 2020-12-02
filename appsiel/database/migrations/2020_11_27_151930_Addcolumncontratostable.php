<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumncontratostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_contratos', function (Blueprint $table) {
            $table->string('estado')->default('ACTIVO')->after('id');
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
            $table->dropColumn('estado');
        });
    }
}
