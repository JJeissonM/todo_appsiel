<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracionfuentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_configuracionfuentes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('fuente_id');
            $table->foreign('fuente_id')->references('id')->on('pw_fuentes')->onDelete('CASCADE');
            $table->unsignedInteger('configuracion_id');
            $table->foreign('configuracion_id')->references('id')->on('pw_configuracion_general')->onDelete('CASCADE');
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
        Schema::drop('pw_configuracionfuentes');
    }
}
