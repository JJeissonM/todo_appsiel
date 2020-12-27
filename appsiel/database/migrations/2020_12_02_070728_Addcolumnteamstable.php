<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnteamstable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_teams', function (Blueprint $table) {
            $table->string('title_color', 40)->after('title');
            $table->string('description_color', 40)->after('description');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_teams', function (Blueprint $table) {
            $table->dropColumn('title_color');
            $table->dropColumn('description_color');
        });
    }
}
