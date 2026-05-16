<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class GrantApmPrintJobsPermissionToAdminRoles extends Migration
{
    protected $permissionName = 'vtas_apm_print_jobs';
    protected $modelNamespace = 'App\\Ventas\\ApmPrintJob';

    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $modeloId = $this->getModeloId($now);
        $coreAppId = $this->getVentasAppId();
        $permissionId = DB::table('permissions')->where('name', $this->permissionName)->value('id');

        if (!$permissionId) {
            $permissionData = [
                'core_app_id' => $coreAppId,
                'modelo_id' => $modeloId,
                'name' => $this->permissionName,
                'descripcion' => 'Trabajos de impresion APM',
                'url' => 'web',
                'parent' => 0,
                'orden' => 100,
                'enabled' => 0,
                'fa_icon' => 'print',
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
                    'descripcion' => 'Trabajos de impresion APM',
                    'url' => 'web',
                    'parent' => 0,
                    'orden' => 100,
                    'fa_icon' => 'print',
                    'updated_at' => $now,
                ]);
        }

        $this->grantPermissionToAdminRoles($permissionId);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        if (!Schema::hasTable('permissions') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $permissionId = DB::table('permissions')->where('name', $this->permissionName)->value('id');

        if ($permissionId) {
            $roleIds = DB::table('roles')
                ->whereIn('name', ['SuperAdmin', 'Administrador'])
                ->pluck('id')
                ->toArray();

            if (!empty($roleIds)) {
                DB::table('role_has_permissions')
                    ->where('permission_id', $permissionId)
                    ->whereIn('role_id', $roleIds)
                    ->delete();
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function getModeloId($now)
    {
        if (!Schema::hasTable('sys_modelos')) {
            return 0;
        }

        $modeloId = DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');

        if ($modeloId) {
            return $modeloId;
        }

        $modelData = [
            'descripcion' => 'Trabajos de impresion APM',
            'modelo' => 'apm_print_jobs',
            'name_space' => $this->modelNamespace,
            'modelo_relacionado' => '',
            'url_crear' => '',
            'url_edit' => '',
            'url_print' => '',
            'url_ver' => '',
            'enlaces' => '',
            'url_estado' => '',
            'url_eliminar' => '',
            'controller_complementario' => '',
            'url_form_create' => '',
            'home_miga_pan' => '',
            'ruta_storage_imagen' => '',
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('sys_modelos', 'ruta_storage_archivo_adjunto')) {
            $modelData['ruta_storage_archivo_adjunto'] = '';
        }

        return DB::table('sys_modelos')->insertGetId($modelData);
    }

    protected function getVentasAppId()
    {
        if (Schema::hasTable('sys_aplicaciones')) {
            $foundAppId = DB::table('sys_aplicaciones')->where('descripcion', 'LIKE', '%Ventas%')->value('id');
            if ($foundAppId) {
                return (int) $foundAppId;
            }
        }

        return 13;
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

            if ($exists) {
                continue;
            }

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
