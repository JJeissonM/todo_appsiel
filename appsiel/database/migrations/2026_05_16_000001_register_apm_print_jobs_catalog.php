<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class RegisterApmPrintJobsCatalog extends Migration
{
    protected $modelNamespace = 'App\\Ventas\\ApmPrintJob';
    protected $modelTable = 'apm_print_jobs';

    public function up()
    {
        if (!Schema::hasTable('sys_modelos')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $modeloId = DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');

        if (!$modeloId) {
            $modelData = [
                'descripcion' => 'Trabajos de impresion APM',
                'modelo' => $this->modelTable,
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

            $modeloId = DB::table('sys_modelos')->insertGetId($modelData);
        }

        $this->registerPermission($modeloId, $now);
    }

    public function down()
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'vtas_apm_print_jobs')->delete();
        }

        if (Schema::hasTable('sys_modelos')) {
            DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->delete();
        }
    }

    protected function registerPermission($modeloId, $now)
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 13;
        if (Schema::hasTable('sys_aplicaciones')) {
            $foundAppId = DB::table('sys_aplicaciones')->where('descripcion', 'LIKE', '%Ventas%')->value('id');
            if ($foundAppId) {
                $coreAppId = (int) $foundAppId;
            }
        }

        $permissionId = DB::table('permissions')->where('name', 'vtas_apm_print_jobs')->value('id');

        if (!$permissionId) {
            $permissionData = [
                'core_app_id' => $coreAppId,
                'modelo_id' => $modeloId,
                'name' => 'vtas_apm_print_jobs',
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
