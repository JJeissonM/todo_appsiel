<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaImpuestosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('siesa_impuestos')) {
            Schema::create('siesa_impuestos', function (Blueprint $table) {
                $table->increments('id');
                $table->string('clase')->nullable();
                $table->string('descripcion')->nullable();
                $table->string('sigla')->nullable();
                $table->string('gravado')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('siesa_impuestos')) {
            Schema::drop('siesa_impuestos');
        }
    }
}
