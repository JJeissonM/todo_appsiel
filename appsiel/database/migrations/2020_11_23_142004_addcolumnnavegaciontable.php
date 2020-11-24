<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnnavegaciontable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_navegacion', function (Blueprint $table) {
            $table->double('alpha')->after('fixed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_navegacion', function (Blueprint $table) {
            $table->dropColumn('alpha');
        });
    }
}
