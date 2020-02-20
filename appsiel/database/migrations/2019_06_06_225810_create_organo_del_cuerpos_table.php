<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrganoDelCuerposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salud_organos_del_cuerpo', function (Blueprint $table) {
            $table->increments('id');
            $table->string('descripcion');
            $table->longtext('detalle');
            $table->integer('organo_padre_id')->unsigned()->index();
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
        Schema::drop('salud_organos_del_cuerpo');
    }
}
