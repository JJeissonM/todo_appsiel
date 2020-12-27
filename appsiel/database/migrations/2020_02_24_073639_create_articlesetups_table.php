<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticlesetupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_articlesetups', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('descripcion')->nullable();
            $table->string('formato', 50)->default('LISTA'); //LISTA, BLOG
            $table->string('orden', 50)->default('ASC'); //ASC: Antiguos primero, DESC: Mas recientes primero
            $table->unsignedInteger('widget_id');
            $table->foreign('widget_id')->references('id')->on('pw_widget')->onDelete('CASCADE');
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
        Schema::drop('articlesetups');
    }
}
