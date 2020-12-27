<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVariableExamensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_variables_examenes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('descripcion')->unsigned()->index();
            $table->integer('abreviatura')->unsigned()->index();
            $table->string('orden');
            $table->string('estado');
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
        Schema::drop('salud_variables_examenes');
    }
}
