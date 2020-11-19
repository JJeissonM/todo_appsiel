<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumn2footertable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_footer', function (Blueprint $table) {
            $table->string('ondas', 5)->after('background2');
            $table->string('animacion')->after('ondas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_footer', function (Blueprint $table) {
            $table->dropColumn('ondas');
            $table->dropColumn('animacion');
        });
    }
}
