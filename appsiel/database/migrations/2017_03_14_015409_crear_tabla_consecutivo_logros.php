<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaConsecutivoLogros extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ConsecutivoLogro', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_colegio');
            $table->integer('ultimo_logro');
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
        Schema::drop('ConsecutivoLogro');
    }
}
