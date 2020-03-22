<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanillacsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_planillacs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('razon_social'); //entidad del software
            $table->string('nit');
            $table->string('convenio');
            $table->unsignedInteger('contrato_id'); //contrato
            $table->foreign('contrato_id')->references('id')->on('cte_contratos')->onDelete('CASCADE');
            $table->unsignedInteger('plantilla_id'); //plantilla
            $table->foreign('plantilla_id')->references('id')->on('cte_plantillas')->onDelete('CASCADE');
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
        Schema::drop('planillacs');
    }
}
