<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TablaPwCustomHtml extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_custom_html',function (Blueprint $table){
            $table->increments('id');
            $table->longtext('contenido');
            $table->longtext('estilos');
            $table->longtext('scripts');
            $table->longtext('links');
            $table->longtext('parametros');
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
        Schema::drop('pw_custom_html');
    }
}
