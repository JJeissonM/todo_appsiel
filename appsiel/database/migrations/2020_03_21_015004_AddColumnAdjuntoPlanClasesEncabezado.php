<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnAdjuntoPlanClasesEncabezado extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sga_plan_clases_encabezados', function(Blueprint $table){
          $table->string('archivo_adjunto')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sga_plan_clases_encabezados', function(Blueprint $table){
            $table->dropColumn('archivo_adjunto');
        });
    }
}
