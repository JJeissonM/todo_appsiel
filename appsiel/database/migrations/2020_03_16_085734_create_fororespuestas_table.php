<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFororespuestasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_fororespuestas', function (Blueprint $table) {
            $table->increments('id');
            $table->text('contenido');
            $table->unsignedInteger('user_id'); //autor
            $table->foreign('user_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->unsignedInteger('foro_id'); //foro
            $table->foreign('foro_id')->references('id')->on('core_foros')->onDelete('CASCADE');
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
        Schema::drop('fororespuestas');
    }
}
