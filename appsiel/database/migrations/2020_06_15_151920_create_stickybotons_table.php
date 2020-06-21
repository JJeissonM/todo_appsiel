<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStickybotonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_stickybotons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('color');
            $table->string('icono')->nullable();
            $table->string('enlace')->nullable();
            $table->string('texto')->nullable();
            $table->unsignedInteger('sticky_id');
            $table->foreign('sticky_id')->references('id')->on('pw_stickies')->onDelete('CASCADE');
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
        Schema::drop('pw_stickybotons');
    }
}
