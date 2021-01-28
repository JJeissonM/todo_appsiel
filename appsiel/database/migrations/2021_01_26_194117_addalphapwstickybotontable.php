<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addalphapwstickybotontable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_stickybotons', function (Blueprint $table) {
            $table->double('alpha')->after('imagen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_stickybotons', function (Blueprint $table) {
            $table->dropColumn('alpha');
        });
    }
}
