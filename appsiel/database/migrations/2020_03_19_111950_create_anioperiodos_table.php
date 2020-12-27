<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAnioperiodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_anioperiodos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('inicio');
            $table->string('fin');
            $table->unsignedInteger('anio_id'); //anio
            $table->foreign('anio_id')->references('id')->on('cte_anios')->onDelete('CASCADE');
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
        Schema::drop('anioperiodos');
    }
}
