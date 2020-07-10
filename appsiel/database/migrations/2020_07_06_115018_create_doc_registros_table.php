<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDocRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_pos_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('vtas_pos_doc_encabezado_id')->unsigned()->index();
            $table->integer('vtas_motivo_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->double('precio_unitario');
            $table->double('cantidad');
            $table->double('precio_total');
            $table->double('base_impuesto');
            $table->double('tasa_impuesto');
            $table->double('valor_impuesto');
            $table->double('base_impuesto_total');
            $table->double('tasa_descuento');
            $table->double('valor_total_descuento');
            $table->double('cantidad_devuelta');
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
        Schema::drop('vtas_pos_doc_registros');
    }
}
