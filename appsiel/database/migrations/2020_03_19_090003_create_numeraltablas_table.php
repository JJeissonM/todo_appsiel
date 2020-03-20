<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNumeraltablasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_numeraltablas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('campo');
            $table->string('valor');
            $table->unsignedInteger('plantillaarticulonumeral_id'); //plantillaarticulonumeral
            $table->foreign('plantillaarticulonumeral_id')->references('id')->on('cte_plantillaarticulonumerals')->onDelete('CASCADE');
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
        Schema::drop('numeraltablas');
    }
}
