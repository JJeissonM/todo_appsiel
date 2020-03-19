<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Header extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_header',function (Blueprint $table){
           $table->increments('id');
           $table->string('disposicion',30);
           $table->string('titulo',30);
           $table->string('descripcion',50);
           $table->string('enlace');
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
        Schema::drop('pw_header');
    }
}
