<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanClaseEstrucElementosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_plan_clases_struc_elementos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('plantilla_plan_clases_id')->unsigned()->index();
            $table->string('descripcion');
            $table->integer('orden');
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
        Schema::drop('sga_plan_clases_struc_elementos');
    }
}
