<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturaPosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vtas_pos_doc_encabezados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('core_tipo_transaccion_id')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id')->unsigned()->index();
            $table->integer('consecutivo');
            $table->date('fecha');
            $table->integer('core_empresa_id')->unsigned()->index();
            $table->integer('core_tercero_id')->unsigned()->index();
            $table->integer('remision_doc_encabezado_id')->unsigned()->index();
            $table->integer('ventas_doc_relacionado_id')->unsigned()->index();
            $table->integer('cliente_id')->unsigned()->index();
            $table->integer('vendedor_id')->unsigned()->index();
            $table->integer('pdv_id')->unsigned()->index();
            $table->integer('cajero_id')->unsigned()->index();
            $table->string('forma_pago');
            $table->dateTime('fecha_entrega');
            $table->date('fecha_vencimiento');
            $table->string('orden_compras');
            $table->longtext('descripcion');
            $table->double('valor_total');
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
        Schema::drop('vtas_pos_doc_encabezados');
    }
}
