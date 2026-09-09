<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddChequeraTraceToTesoControlCheques extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('teso_control_cheques')) {
            return;
        }

        Schema::table('teso_control_cheques', function (Blueprint $table) {
            if (!Schema::hasColumn('teso_control_cheques', 'teso_chequera_id')) {
                $table->unsignedInteger('teso_chequera_id')->nullable()->after('teso_caja_id');
            }
            if (!Schema::hasColumn('teso_control_cheques', 'teso_cuenta_bancaria_id')) {
                $table->unsignedInteger('teso_cuenta_bancaria_id')->nullable()->after('teso_chequera_id');
            }
            if (!Schema::hasColumn('teso_control_cheques', 'teso_doc_encabezado_id')) {
                $table->unsignedInteger('teso_doc_encabezado_id')->nullable()->after('teso_cuenta_bancaria_id');
            }
            if (!Schema::hasColumn('teso_control_cheques', 'modalidad')) {
                $table->string('modalidad', 30)->nullable()->after('fuente');
            }
        });

        Schema::table('teso_control_cheques', function (Blueprint $table) {
            $table->index('teso_chequera_id', 'idx_control_cheques_chequera');
            $table->index('teso_cuenta_bancaria_id', 'idx_control_cheques_cuenta');
            $table->index('teso_doc_encabezado_id', 'idx_control_cheques_documento');
            $table->unique(['teso_chequera_id', 'numero_cheque'], 'uq_control_cheques_chequera_numero');

            $table->foreign('teso_chequera_id', 'fk_control_cheques_chequera')
                ->references('id')->on('teso_chequeras');
            $table->foreign('teso_cuenta_bancaria_id', 'fk_control_cheques_cuenta')
                ->references('id')->on('teso_cuentas_bancarias');
            $table->foreign('teso_doc_encabezado_id', 'fk_control_cheques_documento')
                ->references('id')->on('teso_doc_encabezados');
        });
    }

    public function down()
    {
        if (!Schema::hasTable('teso_control_cheques')) {
            return;
        }

        Schema::table('teso_control_cheques', function (Blueprint $table) {
            if (Schema::hasColumn('teso_control_cheques', 'teso_chequera_id')) {
                $table->dropForeign('fk_control_cheques_chequera');
                $table->dropUnique('uq_control_cheques_chequera_numero');
                $table->dropIndex('idx_control_cheques_chequera');
            }
            if (Schema::hasColumn('teso_control_cheques', 'teso_cuenta_bancaria_id')) {
                $table->dropForeign('fk_control_cheques_cuenta');
                $table->dropIndex('idx_control_cheques_cuenta');
            }
            if (Schema::hasColumn('teso_control_cheques', 'teso_doc_encabezado_id')) {
                $table->dropForeign('fk_control_cheques_documento');
                $table->dropIndex('idx_control_cheques_documento');
            }
        });

        Schema::table('teso_control_cheques', function (Blueprint $table) {
            $columnas = [];
            foreach (['teso_chequera_id', 'teso_cuenta_bancaria_id', 'teso_doc_encabezado_id', 'modalidad'] as $columna) {
                if (Schema::hasColumn('teso_control_cheques', $columna)) {
                    $columnas[] = $columna;
                }
            }
            if (!empty($columnas)) {
                $table->dropColumn($columnas);
            }
        });
    }
}
