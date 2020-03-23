<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMantobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_mantobs', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_suceso')->nullable();
            $table->text('observacion');
            $table->unsignedInteger('mantenimiento_id');
            $table->foreign('mantenimiento_id')->references('id')->on('cte_mantenimientos')->onDelete('CASCADE');
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
        Schema::drop('mantobs');
    }
}
