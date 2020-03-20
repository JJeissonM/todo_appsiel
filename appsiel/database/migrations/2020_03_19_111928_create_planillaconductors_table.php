<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlanillaconductorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_planillaconductors', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('conductor_id'); //conductor
            $table->foreign('conductor_id')->references('id')->on('cte_conductors')->onDelete('CASCADE');
            $table->unsignedInteger('planillac_id'); //planillac
            $table->foreign('planillac_id')->references('id')->on('cte_planillacs')->onDelete('CASCADE');
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
        Schema::drop('planillaconductors');
    }
}
