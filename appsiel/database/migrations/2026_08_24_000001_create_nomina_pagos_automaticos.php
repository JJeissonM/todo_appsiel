<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateNominaPagosAutomaticos extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nom_pagos_automaticos')) {
            Schema::create('nom_pagos_automaticos', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('core_empresa_id');
                $table->unsignedInteger('nom_doc_encabezado_id');
                $table->unsignedInteger('teso_doc_encabezado_id')->nullable();
                $table->unsignedInteger('teso_medio_recaudo_id');
                $table->unsignedInteger('teso_caja_id')->nullable();
                $table->unsignedInteger('teso_cuenta_bancaria_id')->nullable();
                $table->date('fecha_pago');
                $table->decimal('valor_total', 20, 2)->default(0);
                $table->unsignedInteger('cantidad_empleados')->default(0);
                $table->string('estado', 20)->default('Generado');
                $table->string('token_solicitud', 80);
                $table->string('creado_por');
                $table->string('modificado_por')->default('');
                $table->timestamps();

                $table->unique('token_solicitud', 'nom_pago_auto_token_unique');
                $table->index(['core_empresa_id', 'nom_doc_encabezado_id'], 'nom_pago_auto_documento_idx');
                $table->index('teso_doc_encabezado_id', 'nom_pago_auto_pago_idx');
            });
        }

        if (!Schema::hasTable('nom_pagos_automaticos_detalles')) {
            Schema::create('nom_pagos_automaticos_detalles', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('nom_pago_automatico_id');
                $table->unsignedInteger('nom_contrato_id')->nullable();
                $table->unsignedInteger('core_tercero_id');
                $table->unsignedInteger('cxp_movimiento_id');
                $table->unsignedInteger('cxp_abono_id')->nullable();
                $table->decimal('valor_pagado', 20, 2);
                $table->timestamps();

                $table->unique(
                    ['nom_pago_automatico_id', 'cxp_movimiento_id'],
                    'nom_pago_auto_det_mov_unique'
                );
                $table->index('core_tercero_id', 'nom_pago_auto_det_tercero_idx');
            });
        }

        $this->crearPermiso();
    }

    public function down()
    {
        if (Schema::hasTable('permissions')) {
            $permission = Permission::where('name', 'nomina.procesos.generar_pagos_nomina')->first();
            if (!is_null($permission)) {
                $permission->delete();
            }
        }

        Schema::dropIfExists('nom_pagos_automaticos_detalles');
        Schema::dropIfExists('nom_pagos_automaticos');
    }

    protected function crearPermiso()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 0;
        if (Schema::hasTable('sys_aplicaciones')) {
            $coreAppId = (int) DB::table('sys_aplicaciones')
                ->whereIn('descripcion', ['Nómina', 'Nomina'])
                ->value('id');
        }

        $permission = Permission::firstOrNew(['name' => 'nomina.procesos.generar_pagos_nomina']);
        $permission->core_app_id = $coreAppId;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Generar pagos de nómina';
        $permission->url = 'index_procesos/nomina.procesos.generar_pagos_nomina';
        $permission->parent = 0;
        $permission->orden = 0;
        $permission->enabled = 1;
        $permission->fa_icon = 'fa fa-credit-card';
        $permission->save();

        foreach (['SuperAdmin', 'Administrador', 'Admin Colegio'] as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($permission);
        }
    }
}
