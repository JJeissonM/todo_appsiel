<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoreFirmasAutorizadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('core_firmas_autorizadas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_persona')->unsigned()->index();
            $table->string('titulo_persona');
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
        Schema::drop('core_firmas_autorizadas');
    }
}
