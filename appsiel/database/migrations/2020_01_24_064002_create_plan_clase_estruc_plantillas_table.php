<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanClaseEstrucPlantillasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_plan_clases_struc_plantillas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('periodo_lectivo_id')->unsigned()->index();
            $table->string('descripcion');
            $table->longtext('detalle');
            $table->string('estado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sga_plan_clases_struc_plantillas');
    }
}
