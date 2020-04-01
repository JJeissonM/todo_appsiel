<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorreosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_correos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre_remitente')->nullable();
            $table->string('email_remitente');
            $table->string('color_base',10)->default('#ffffff');
            $table->string('color_fondo',10)->default('#ffffff');
            $table->string('color_texto',10)->default('#000000');
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
        Schema::drop('pw_correos');
    }
}
