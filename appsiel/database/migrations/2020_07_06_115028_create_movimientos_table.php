<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovimientosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_pos_movimientos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('pdv_id')->unsigned()->index();
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('codigo_referencia_tercero')->unsigned()->index();
            $table->integer('remision_doc_encabezado_id')->unsigned()->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->integer('vendedor_id')->unsigned()->index();
            $table->integer('cajero_id')->unsigned()->index();
            $table->integer('zona_id')->unsigned()->index();
            $table->integer('clase_cliente_id')->unsigned()->index();
            $table->integer('equipo_ventas_id')->unsigned()->index();
            $table->string('forma_pago');
            $table->date('fecha_vencimiento');
            $table->string('orden_compras');
            $table->integer('inv_producto_id')->unsigned()->index();
            $table->integer('inv_bodega_id')->unsigned()->index();
            $table->integer('vtas_motivo_id')->unsigned()->index();
            $table->integer('inv_motivo_id')->unsigned()->index();
            $table->double('precio_unitario');
            $table->double('cantidad');
            $table->double('precio_total');
            $table->double('base_impuesto');
            $table->double('tasa_impuesto');
            $table->double('valor_impuesto');
            $table->double('base_impuesto_total');
            $table->double('tasa_descuento');
            $table->double('valor_total_descuento');
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
        Schema::drop('vtas_pos_movimientos');
    }
}
