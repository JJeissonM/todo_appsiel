<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiesaRetencionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (!Schema::hasTable('siesa_retenciones')) {
            Schema::create('siesa_retenciones', function (Blueprint $table) {
                $table->increments('id');
                $table->string('clase')->nullable();
                $table->string('descripcion')->nullable();
                $table->string('sigla')->nullable();
                $table->string('estado')->nullable();
                $table->string('regionalidad')->nullable();
                $table->string('baseimpto')->nullable();
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
        if (env('APPSIEL_CLIENTE') !== 'SIESA') {
            return;
        }
        if (Schema::hasTable('siesa_retenciones')) {
            Schema::drop('siesa_retenciones');
        }
    }
}


