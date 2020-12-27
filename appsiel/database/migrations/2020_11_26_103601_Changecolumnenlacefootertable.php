<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Changecolumnenlacefootertable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_enlace_footer', function (Blueprint $table) {
            $table->string('texto')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_enlace_footer', function (Blueprint $table) {
            $table->dropColumn('texto');
        });
    }
}
