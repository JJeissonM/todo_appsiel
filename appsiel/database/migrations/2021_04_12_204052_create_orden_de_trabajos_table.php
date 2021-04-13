<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdenDeTrabajosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nom_ordenes_de_trabajo', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->integer('nom_doc_encabezado_id')->unsigned()->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->date('fecha');
            $table->longtext('descripcion');
            $table->integer('nom_concepto_id')->unsigned()->index();
            $table->string('ubicacion_desarrollo_actividad');
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
        Schema::drop('nom_ordenes_de_trabajo');
    }
}
