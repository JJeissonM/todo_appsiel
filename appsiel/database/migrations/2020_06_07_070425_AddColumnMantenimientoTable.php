<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnMantenimientoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_mantenimientos', function (Blueprint $table) {
            $table->string('documento')->nullable()->after('sede');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_mantenimientos', function (Blueprint $table) {
            $table->dropColumn('documento');
        });
    }
}
