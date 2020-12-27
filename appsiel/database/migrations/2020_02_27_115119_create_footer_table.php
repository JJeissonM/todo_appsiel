<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFooterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_footer',function (Blueprint $table){
            $table->increments('id');
            $table->longText('ubicacion');
            $table->string('copyright');
            $table->string('texto',100);
            $table->string('background',20);
            $table->string('color',20);
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
        Schema::drop('pw_footer');
    }
}
