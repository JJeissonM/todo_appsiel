<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemtestimonialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_itemtestimonials', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->string('testimonio');
            $table->string('cargo');
            $table->string('foto');
            $table->unsignedInteger('testimoniale_id');
            $table->foreign('testimoniale_id')->references('id')->on('pw_testimoniales')->onDelete('cascade');
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
        Schema::drop('pw_itemtestimonials');
    }
}
