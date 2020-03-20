<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateContratogrupousTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_contratogrupous', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identificacion');
            $table->string('persona');
            $table->unsignedInteger('contrato_id'); //contrato
            $table->foreign('contrato_id')->references('id')->on('cte_contratos')->onDelete('CASCADE');
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
        Schema::drop('contratogrupous');
    }
}
