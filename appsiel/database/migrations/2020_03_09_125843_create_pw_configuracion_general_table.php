<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePwConfiguracionGeneralTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_configuracion_general', function(Blueprint $table)
        {
            $table->increments('id');
            $table->string('color_primario',10);
            $table->string('color_segundario',10);
            $table->string('color_terciario',10);
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
        Schema::drop('pw_configuracion_general');
    }
}
