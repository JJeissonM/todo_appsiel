<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTipoToSeccion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_seccion', function(Blueprint $table){
          $table->enum('tipo',['ESTANDAR','GENERICO'])->default('GENERICO');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_seccion', function(Blueprint $table){
            $table->dropColumn('tipo');
        });
    }
}
