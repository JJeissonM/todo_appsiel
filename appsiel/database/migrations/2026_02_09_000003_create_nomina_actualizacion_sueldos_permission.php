<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateNominaActualizacionSueldosPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 0;
        if (DB::getSchemaBuilder()->hasTable('sys_aplicaciones')) {
            $coreAppId = (int) DB::table('sys_aplicaciones')
                ->where('descripcion', 'Nómina')
                ->value('id');

            if ($coreAppId == 0) {
                $coreAppId = (int) DB::table('sys_aplicaciones')
                    ->where('descripcion', 'Nomina')
                    ->value('id');
            }
        }

        $permission = Permission::firstOrNew([
            'name' => 'nomina.procesos.actualizar_sueldos_contratos'
        ]);

        $permission->core_app_id = $coreAppId;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Actualizar sueldos en contratos';
        $permission->url = 'index_procesos/nomina.procesos.actualizar_sueldos_contratos';
        $permission->parent = 0;
        $permission->orden = 0;
        $permission->enabled = 1;
        $permission->fa_icon = 'fa fa-money';
        $permission->save();

        $roles = [
            'SuperAdmin',
            'Administrador',
            'Admin Colegio'
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($permission);
        }
    }

    public function down()
    {
        $permission = Permission::where('name', 'nomina.procesos.actualizar_sueldos_contratos')->first();
        if (!is_null($permission)) {
            $permission->delete();
        }
    }
}
