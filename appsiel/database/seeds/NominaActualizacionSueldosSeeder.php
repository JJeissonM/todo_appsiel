<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class NominaActualizacionSueldosSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
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
        $permission->parent = 448; // ID del permiso padre "Procesos"
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
            if (!$this->roleHasPermission($role->id, $permission->id)) {
                $role->givePermissionTo($permission);
            }
        }
    }

    private function roleHasPermission($roleId, $permissionId)
    {
        if (!Schema::hasTable('role_has_permissions')) {
            return true;
        }

        return DB::table('role_has_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();
    }
}
