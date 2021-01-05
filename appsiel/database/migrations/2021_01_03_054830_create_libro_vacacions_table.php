<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLibroVacacionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_libro_vacaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('nom_contrato_id')->unsigned()->index();
            $table->integer('nom_doc_encabezado_id')->unsigned()->index();
            $table->date('periodo_pagado_desde');
            $table->date('periodo_pagado_hasta');
            $table->date('periodo_disfrute_vacacion_desde');
            $table->date('periodo_disfrute_vacacion_hasta');
            $table->integer('dias_pagados');
            $table->integer('dias_compensados');
            $table->integer('dias_disfrutados');
            $table->double('valor_vacaciones');
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
        Schema::drop('nom_libro_vacaciones');
    }
}
