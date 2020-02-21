<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFotosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_fotos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nombre');
            $table->unsignedBigInteger('album_id');
            $table->foreign('album_id')->references('id')->on('pw_albums')->onDelete('CASCADE');
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
        Schema::drop('fotos');
    }
}
