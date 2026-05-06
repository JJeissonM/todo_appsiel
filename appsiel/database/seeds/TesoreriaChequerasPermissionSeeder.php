<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TesoreriaChequerasPermissionSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('roles')) {
            return;
        }

        $coreAppId = 0;
        if (Schema::hasTable('sys_aplicaciones')) {
            $coreAppId = (int)DB::table('sys_aplicaciones')
                ->where('descripcion', 'Tesorería')
                ->value('id');

            if ($coreAppId == 0) {
                $coreAppId = (int)DB::table('sys_aplicaciones')
                    ->where('descripcion', 'Tesoreria')
                    ->value('id');
            }
        }

        $permission = Permission::firstOrNew([
            'name' => 'tesoreria.chequeras.gestionar'
        ]);

        $permission->core_app_id = $coreAppId;
        $permission->modelo_id = 33;
        $permission->descripcion = 'Gestionar chequeras de cuentas bancarias';
        $permission->url = 'teso_cuentas_bancarias';
        $permission->parent = 0;
        $permission->orden = 0;
        $permission->enabled = 0;
        $permission->fa_icon = 'fa fa-book';
        $permission->save();

        foreach (['SuperAdmin', 'Administrador'] as $roleName) {
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
