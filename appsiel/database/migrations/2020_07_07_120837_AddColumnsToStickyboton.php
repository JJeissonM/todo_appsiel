<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToStickyboton extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_stickybotons', function (Blueprint $table) {
            $table->string('imagen')->nullable()->after('texto');
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
            $table->dropColumn('imagen');
        });
    }
}
