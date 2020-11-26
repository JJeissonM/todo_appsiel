<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnitemslidertable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_itemslider', function (Blueprint $table) {
            $table->string('colorTitle', 20)->after('enlace');
            $table->string('colorText', 20)->after('colorTitle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_itemslider', function (Blueprint $table) {
            $table->dropColumn('colorTitle');
            $table->dropColumn('colorText');
        });
    }
}
