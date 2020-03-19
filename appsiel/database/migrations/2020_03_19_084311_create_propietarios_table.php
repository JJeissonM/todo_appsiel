<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropietariosTable extends Migration
{
    /**
     * Run the migrations.
     *-
     * @return void
     */
    public function up()
    {
        Schema::create('cte_propietarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('genera_planilla', 10)->default('SI'); //SI, NO
            $table->unsignedInteger('tercero_id'); //foro
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
        Schema::drop('propietarios');
    }
}
