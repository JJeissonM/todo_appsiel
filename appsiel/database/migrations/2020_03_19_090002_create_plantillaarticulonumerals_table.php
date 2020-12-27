<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlantillaarticulonumeralsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_plantillaarticulonumerals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('numeracion');
            $table->text('texto');
            $table->unsignedInteger('plantillaarticulo_id'); //planillaarticulo
            $table->foreign('plantillaarticulo_id')->references('id')->on('cte_plantillaarticulos')->onDelete('CASCADE');
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
        Schema::drop('plantillaarticulonumerals');
    }
}
