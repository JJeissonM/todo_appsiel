<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComprasDocEncabezadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('compras_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('cotizacion_doc_encabezado_id')->unsigned()->index();
            $table->integer('proveedor_id')->unsigned()->index();
            $table->integer('comprador_id')->unsigned()->index();
            $table->integer('condicion_pago_id')->unsigned()->index();
            $table->date('fecha_entrega');
            $table->longtext('detalle');
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
        Schema::drop('compras_doc_encabezados');
    }
}
