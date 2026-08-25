<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateOperationalShiftsCore extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('core_turno_configuraciones')) {
            Schema::create('core_turno_configuraciones', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('core_empresa_id');
                $table->string('modulo', 50)->default('*');
                $table->string('contexto_tipo', 50)->default('*');
                $table->unsignedInteger('contexto_id')->default(0);
                $table->string('modo', 20)->default('TRADICIONAL');
                $table->string('creado_por')->nullable();
                $table->string('modificado_por')->nullable();
                $table->timestamps();
                $table->unique(array('core_empresa_id', 'modulo', 'contexto_tipo', 'contexto_id'), 'turno_config_scope_uq');
                $table->index(array('core_empresa_id', 'modo'), 'turno_config_empresa_modo_idx');
            });
        }

        if (!Schema::hasTable('core_turnos_operativos')) {
            Schema::create('core_turnos_operativos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('core_empresa_id');
                $table->string('contexto_tipo', 50);
                $table->unsignedInteger('contexto_id');
                $table->unsignedInteger('pdv_id')->nullable();
                $table->unsignedInteger('teso_caja_id')->nullable();
                $table->date('fecha_operativa');
                $table->dateTime('abierto_en');
                $table->dateTime('cerrado_en')->nullable();
                $table->unsignedInteger('abierto_por')->nullable();
                $table->unsignedInteger('cerrado_por')->nullable();
                $table->decimal('saldo_inicial', 18, 2)->default(0);
                $table->decimal('saldo_cierre', 18, 2)->nullable();
                $table->string('estado', 20)->default('ABIERTO');
                $table->string('codigo', 80)->unique();
                // Solo los turnos abiertos conservan esta clave. El NULL de los cerrados
                // permite multiples turnos historicos para el mismo contexto.
                $table->string('clave_contexto_abierto', 120)->nullable()->unique();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->index(array('core_empresa_id', 'contexto_tipo', 'contexto_id', 'estado'), 'turno_contexto_estado_idx');
                $table->index(array('core_empresa_id', 'fecha_operativa'), 'turno_empresa_fecha_idx');
            });
        }

        if (!Schema::hasTable('core_turno_eventos')) {
            Schema::create('core_turno_eventos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('turno_operativo_id');
                $table->string('tipo', 30);
                $table->string('estado_anterior', 20)->nullable();
                $table->string('estado_nuevo', 20)->nullable();
                $table->string('entidad_tipo', 120)->nullable();
                $table->unsignedInteger('entidad_id')->nullable();
                $table->unsignedInteger('usuario_id')->nullable();
                $table->text('motivo')->nullable();
                $table->text('datos')->nullable();
                $table->timestamps();
                $table->index(array('turno_operativo_id', 'tipo'), 'turno_evento_turno_tipo_idx');
                $table->index(array('entidad_tipo', 'entidad_id'), 'turno_evento_entidad_idx');
            });
        }

        $this->addTurnColumn('vtas_pos_apertura_encabezados');
        $this->addTurnColumn('vtas_pos_cierre_encabezados');
        $this->addTurnColumn('vtas_pos_doc_encabezados');
        $this->addTurnColumn('vtas_doc_encabezados');
        $this->addTurnColumn('teso_doc_encabezados');
        $this->addTurnColumn('teso_arqueos_caja');
        $this->addTurnColumn('teso_movimientos');
        $this->addTurnColumn('inv_doc_encabezados');
        $this->addTurnColumn('inv_movimientos');
        $this->addTurnColumn('hotel_order_headers');
    }

    protected function addTurnColumn($tableName)
    {
        if (!Schema::hasTable($tableName) || Schema::hasColumn($tableName, 'turno_operativo_id')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName) {
            $table->unsignedInteger('turno_operativo_id')->nullable();
            $table->index('turno_operativo_id', substr($tableName, 0, 42) . '_turno_idx');
        });
    }

    public function down()
    {
        foreach (array('vtas_pos_apertura_encabezados', 'vtas_pos_cierre_encabezados', 'vtas_pos_doc_encabezados', 'vtas_doc_encabezados', 'teso_movimientos', 'inv_movimientos', 'inv_doc_encabezados', 'hotel_order_headers', 'teso_arqueos_caja') as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'turno_operativo_id')) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    $table->dropIndex(substr($tableName, 0, 42) . '_turno_idx');
                    $table->dropColumn('turno_operativo_id');
                });
            }
        }

        Schema::dropIfExists('core_turno_eventos');
        Schema::dropIfExists('core_turnos_operativos');
        Schema::dropIfExists('core_turno_configuraciones');
    }
}
