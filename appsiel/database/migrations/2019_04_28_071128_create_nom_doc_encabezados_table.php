<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNomDocEncabezadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->string('descripcion');
            $table->double('total_devengos');
            $table->double('total_deducciones');
            $table->string('estado');
            $table->string('creado_por');
            $table->string('modificado_por');
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
        Schema::drop('nom_doc_encabezados');
    }
}
