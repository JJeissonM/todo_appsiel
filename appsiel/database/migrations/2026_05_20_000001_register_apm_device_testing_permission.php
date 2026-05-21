<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class RegisterApmDeviceTestingPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $coreAppId = 13;
        $modeloId = 0;

        if (Schema::hasTable('sys_aplicaciones')) {
            $foundAppId = DB::table('sys_aplicaciones')->where('descripcion', 'LIKE', '%Ventas%')->value('id');
            if ($foundAppId) {
                $coreAppId = (int) $foundAppId;
            }
        }

        if (Schema::hasTable('sys_modelos')) {
            $foundModeloId = DB::table('sys_modelos')->where('name_space', 'App\\Ventas\\ApmDevice')->value('id');
            if ($foundModeloId) {
                $modeloId = (int) $foundModeloId;
            }
        }

        $permissionId = DB::table('permissions')->where('name', 'vtas_apm_device_testing')->value('id');

        if (!$permissionId) {
            $permissionData = [
                'core_app_id' => $coreAppId,
                'modelo_id' => $modeloId,
                'name' => 'vtas_apm_device_testing',
                'descripcion' => 'Probar comandos de dispositivos APM',
                'url' => 'web',
                'parent' => 0,
                'orden' => 101,
                'enabled' => 0,
                'fa_icon' => 'plug',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('permissions', 'guard_name')) {
                $permissionData['guard_name'] = 'web';
            }

            $permissionId = DB::table('permissions')->insertGetId($permissionData);
        } else {
            DB::table('permissions')
                ->where('id', $permissionId)
                ->update([
                    'core_app_id' => $coreAppId,
                    'modelo_id' => $modeloId,
                    'descripcion' => 'Probar comandos de dispositivos APM',
                    'url' => 'web',
                    'parent' => 0,
                    'orden' => 101,
                    'fa_icon' => 'plug',
                    'updated_at' => $now,
                ]);
        }

        $this->grantPermissionToAdminRoles($permissionId);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'vtas_apm_device_testing')->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    protected function grantPermissionToAdminRoles($permissionId)
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $roles = DB::table('roles')->whereIn('name', ['SuperAdmin', 'Administrador'])->get();

        foreach ($roles as $role) {
            $exists = DB::table('role_has_permissions')
                ->where('permission_id', $permissionId)
                ->where('role_id', $role->id)
                ->exists();

            if (!$exists) {
                $data = [
                    'permission_id' => $permissionId,
                    'role_id' => $role->id,
                ];

                if (Schema::hasColumn('role_has_permissions', 'orden')) {
                    $data['orden'] = 0;
                }

                DB::table('role_has_permissions')->insert($data);
            }
        }
    }
}
