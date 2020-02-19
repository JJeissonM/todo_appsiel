<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSgaConfiguracionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sga_configuraciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_grado_quinto')->unsigned()->index();
            $table->integer('id_formato_certificado_quinto')->unsigned()->index();
            $table->integer('id_periodo_certificado_quinto')->unsigned()->index();
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
        Schema::drop('sga_configuraciones');
    }
}
