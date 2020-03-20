<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMantreportesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cte_mantreportes', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fecha_suceso')->nullable();
            $table->text('reporte');
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
        Schema::drop('mantreportes');
    }
}
