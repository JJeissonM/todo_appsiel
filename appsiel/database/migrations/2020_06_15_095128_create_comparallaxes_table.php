<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComparallaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_comparallaxes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo');
            $table->string('descripcion');
            $table->string('fondo');
            $table->string('modo', 50);
            $table->string('textcolor', 50);
            $table->text('content_html');
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
        Schema::drop('pw_comparallaxes');
    }
}
