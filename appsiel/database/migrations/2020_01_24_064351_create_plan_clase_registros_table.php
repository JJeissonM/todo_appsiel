<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanClaseRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_plan_clases_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plan_clase_encabezado_id')->unsigned()->index();
            $table->string('plan_clase_estruc_elemento_id');
            $table->longtext('contenido');
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
        Schema::drop('sga_plan_clases_registros');
    }
}
