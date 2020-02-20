<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasDocRegistrosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_doc_registros', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('compras_doc_encabezado_id')->unsigned()->index();
            $table->integer('inv_motivo_id')->unsigned()->index();
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->double('precio_unitario');
            $table->double('cantidad');
            $table->double('precio_total');
            $table->double('base_impuesto');
            $table->double('tasa_impuesto');
            $table->double('valor_impuesto');
            $table->double('cantidad_recibida');
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
        Schema::drop('compras_doc_registros');
    }
}
