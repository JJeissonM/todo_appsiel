<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnpwitemserviciostable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_itemservicios', function (Blueprint $table) {
            $table->string('url')->after('icono')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_itemservicios', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
}
