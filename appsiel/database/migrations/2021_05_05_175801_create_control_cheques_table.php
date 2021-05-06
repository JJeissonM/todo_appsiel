<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateControlChequesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teso_control_cheques', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fuente');
            $table->integer('tercero_id')->unsigned()->index();
            $table->date('fecha_generacion');
            $table->date('fecha_activacion');
            $table->string('numero_cheque');
            $table->string('referencia_cheque');
            $table->integer('entidad_financiera_id')->unsigned()->index();
            $table->double('valor');
            $table->longtext('detalle');
            $table->string('creado_por');
            $table->string('modificado_por');
            $table->integer('core_tipo_transaccion_id_origen')->unsigned()->index();
            $table->integer('core_tipo_doc_app_id_origen')->unsigned()->index();
            $table->integer('consecutivo');
            $table->integer('teso_caja_id')->unsigned()->index();
            $table->string('tipo');
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
        Schema::drop('teso_control_cheques');
    }
}
