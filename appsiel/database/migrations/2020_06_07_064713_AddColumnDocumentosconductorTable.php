<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnDocumentosconductorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cte_documentosconductors', function (Blueprint $table) {
            $table->string('categoria')->after('documento');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cte_documentosconductors', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }
}
