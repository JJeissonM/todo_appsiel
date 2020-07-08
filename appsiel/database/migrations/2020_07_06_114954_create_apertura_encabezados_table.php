<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAperturaEncabezadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_pos_apertura_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('cajero_id')->unsigned()->index();
            $table->integer('pdv_id')->unsigned()->index();
            $table->double('efectivo_base');
            $table->longtext('detalle');
            $table->string('creado_por');
            $table->string('modificado_por');
            $table->string('estado');
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
        Schema::drop('vtas_pos_apertura_encabezados');
    }
}
