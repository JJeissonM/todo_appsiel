<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnsAboutusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_aboutuses', function(Blueprint $table){
            $table->string('disposicion')->default('DEFAULT')->after('imagen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_aboutuses', function(Blueprint $table){
            $table->dropColumn('disposicion');
        });
    }
}
