<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_articles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 250);
            $table->text('contenido');
            $table->string('estado', 50)->default('VISIBLE'); //VISIBLE, OCULTO
            $table->unsignedInteger('articlesetup_id');
            $table->foreign('articlesetup_id')->references('id')->on('pw_articlesetups')->onDelete('CASCADE');
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
        Schema::drop('articles');
    }
}
