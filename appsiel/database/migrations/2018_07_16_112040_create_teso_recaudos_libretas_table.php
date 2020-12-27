<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTesoRecaudosLibretasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teso_recaudos_libretas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_libreta')->unsigned()->index();
            $table->date('fecha_recaudo');
            $table->string('tipo_recaudo');
            $table->double('valor _recaudo');
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
        Schema::drop('teso_recaudos_libretas');
    }
}
