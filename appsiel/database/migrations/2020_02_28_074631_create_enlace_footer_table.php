<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEnlaceFooterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_enlace_footer',function(Blueprint $table){
            $table->increments('id');
            $table->string('enlace')->nullable();
            $table->string('texto',60);
            $table->string('icono',30)->nullable();
            $table->unsignedInteger('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('pw_categoria_footer')->onDelete('CASCADE');
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
        Schema::drop('pw_enlace_footer');
    }
}
