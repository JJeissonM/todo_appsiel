<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDisposicionToNavegacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_navegacion',function (Blueprint $table){
            $table->string('disposicion',20)->default('DEFAULT')->after('fixed');
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
            $table->dropColumn('disposicion');
        });
    }
}
