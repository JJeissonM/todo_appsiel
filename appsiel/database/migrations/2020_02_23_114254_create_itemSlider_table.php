<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemSliderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_itemSlider',function(Blueprint $table){
            $table->bigIncrements('id');
            $table->string('imagen');
            $table->string('titulo',50);
            $table->string('descripcion');
            $table->string('button','30');
            $table->string('enlace');
            $table->bigInteger('slider_id')->unsigned();
            $table->foreign('slider_id')->references('id')->on('pw_slider')->onDelete('CASCADE');
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
        Schema::drop('pw_slider');
    }
}
