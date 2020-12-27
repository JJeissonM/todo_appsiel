<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAddColumnsSliderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_slider', function(Blueprint $table){
            $table->string('disposicion')->default('DEFAULT')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_slider', function(Blueprint $table){
            $table->dropColumn('disposicion');
        });
    }
}
