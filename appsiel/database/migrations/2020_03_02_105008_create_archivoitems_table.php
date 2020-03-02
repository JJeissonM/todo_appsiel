<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchivoitemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pw_archivoitems', function (Blueprint $table) {
            $table->increments('id');
            $table->string('file', 250);
            $table->string('estado', 50)->default('VISIBLE'); //VISIBLE, OCULTO
            $table->unsignedInteger('archivo_id');
            $table->foreign('archivo_id')->references('id')->on('pw_archivos')->onDelete('CASCADE');
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
        Schema::drop('archivoitems');
    }
}
