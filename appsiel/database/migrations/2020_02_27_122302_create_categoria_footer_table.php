<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriaFooterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_categoria_footer',function(Blueprint $table){
             $table->increments('id');
             $table->string('texto');
             $table->unsignedInteger('footer_id');
             $table->foreign('footer_id')->references('id')->on('pw_footer')->onDelete('CASCADE');
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
        Schema::drop('pw_categoria_footer');
    }

}
