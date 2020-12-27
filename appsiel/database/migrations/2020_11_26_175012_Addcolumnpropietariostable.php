<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnpropietariostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_propietarios', function (Blueprint $table) {
            $table->string('tipo')->after('estado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_propietarios', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
}
