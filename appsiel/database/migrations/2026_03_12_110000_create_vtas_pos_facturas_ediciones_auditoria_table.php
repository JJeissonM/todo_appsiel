<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVtasPosFacturasEdicionesAuditoriaTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vtas_pos_facturas_ediciones_auditoria')) {
            return;
        }

        Schema::create('vtas_pos_facturas_ediciones_auditoria', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('vtas_pos_doc_encabezado_id');
            $table->unsignedInteger('core_tipo_doc_app_id');
            $table->unsignedInteger('consecutivo');
            $table->string('editado_por', 120);
            $table->dateTime('editado_en');
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->longText('cambios')->nullable();
            $table->longText('datos_antes')->nullable();
            $table->longText('datos_despues')->nullable();
            $table->longText('lineas_antes')->nullable();
            $table->longText('lineas_despues')->nullable();
            $table->timestamps();

            $table->index('vtas_pos_doc_encabezado_id', 'idx_vtas_pos_edit_audit_doc_id');
            $table->index(['core_tipo_doc_app_id', 'consecutivo'], 'idx_vtas_pos_edit_audit_doc_num');
            $table->index('editado_en', 'idx_vtas_pos_edit_audit_editado_en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('vtas_pos_facturas_ediciones_auditoria');
    }
}

