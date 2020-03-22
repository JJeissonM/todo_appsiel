<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_contratantes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('estado');
            $table->unsignedInteger('tercero_id'); //tercero
            $table->foreign('tercero_id')->references('id')->on('core_terceros')->onDelete('CASCADE');
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
        Schema::drop('contratantes');
    }
}
