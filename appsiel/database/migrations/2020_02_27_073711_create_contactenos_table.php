<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContactenosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_contactenos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('empresa');
            $table->string('telefono');
            $table->string('ciudad');
            $table->string('correo');
            $table->string('direccion');
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
        Schema::drop('pw_contactenos');
    }
}
