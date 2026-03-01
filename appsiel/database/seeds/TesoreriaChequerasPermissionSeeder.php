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
        if (!Schema::hasTable('permissions')) {
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
            $role->givePermissionTo($permission);
        }
    }
}
