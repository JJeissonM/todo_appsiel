<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnFixedNavegacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_navegacion',function (Blueprint $table){
            $table->dropColumn(['heigth_logo','width_logo']);
            $table->integer('fixed')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_navegacion',function (Blueprint $table){
            $table->integer('heigth_logo');
            $table->integer('width_logo');
        });
    }
}
