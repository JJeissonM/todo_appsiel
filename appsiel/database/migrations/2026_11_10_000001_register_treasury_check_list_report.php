<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterTreasuryCheckListReport extends Migration
{
    protected $reportUrl = 'teso_listado_cheques';

    protected $fieldNames = array(
        'teso_cheques_fecha_desde',
        'teso_cheques_fecha_hasta',
        'teso_cheques_tercero_id',
    );

    public function up()
    {
        if (!Schema::hasTable('sys_aplicaciones') || !Schema::hasTable('sys_reportes')) {
            return;
        }

        $appId = (int)DB::table('sys_aplicaciones')
            ->where(function ($query) {
                $query->where('app', 'tesoreria')
                    ->orWhere('descripcion', 'Tesorería')
                    ->orWhere('descripcion', 'Tesoreria');
            })
            ->value('id');

        if ($appId === 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $reportId = (int)DB::table('sys_reportes')->where('url_form_action', $this->reportUrl)->value('id');
        $reportData = array(
            'descripcion' => 'Listado de cheques',
            'core_app_id' => $appId,
            'url_form_action' => $this->reportUrl,
            'estado' => 'Activo',
            'updated_at' => $now,
        );

        if ($reportId === 0) {
            $reportData['created_at'] = $now;
            $reportId = (int)DB::table('sys_reportes')->insertGetId($reportData);
        } else {
            DB::table('sys_reportes')->where('id', $reportId)->update($reportData);
        }

        $this->registerFields($reportId, $now);
        $this->registerPermission($appId, $now);
        $this->forgetPermissionCache();
    }

    public function down()
    {
        $reportId = Schema::hasTable('sys_reportes')
            ? (int)DB::table('sys_reportes')->where('url_form_action', $this->reportUrl)->value('id')
            : 0;

        if ($reportId > 0 && Schema::hasTable('sys_reporte_tiene_campos')) {
            DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $reportId)->delete();
        }
        if ($reportId > 0) {
            DB::table('sys_reportes')->where('id', $reportId)->delete();
        }

        if (Schema::hasTable('sys_campos') && Schema::hasTable('sys_reporte_tiene_campos')) {
            foreach ($this->fieldNames as $name) {
                $fieldId = DB::table('sys_campos')->where('name', $name)->value('id');
                if ($fieldId && !DB::table('sys_reporte_tiene_campos')->where('core_campo_id', $fieldId)->exists()) {
                    DB::table('sys_campos')->where('id', $fieldId)->delete();
                }
            }
        }

        if (Schema::hasTable('permissions')) {
            $permissionId = (int)DB::table('permissions')->where('name', $this->reportUrl)->value('id');
            if ($permissionId > 0 && Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
            }
            if ($permissionId > 0) {
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }

        $this->forgetPermissionCache();
    }

    protected function registerFields($reportId, $now)
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_reporte_tiene_campos')) {
            return;
        }

        $fields = array(
            array('Fecha desde', 'date', 'teso_cheques_fecha_desde', '', 1),
            array('Fecha hasta', 'date', 'teso_cheques_fecha_hasta', '', 1),
            array('Tercero', 'select', 'teso_cheques_tercero_id', 'model_App\\Core\\Tercero', 0),
        );

        foreach ($fields as $index => $field) {
            $fieldId = (int)DB::table('sys_campos')->where('name', $field[2])->value('id');
            $data = array(
                'descripcion' => $field[0],
                'tipo' => $field[1],
                'name' => $field[2],
                'opciones' => $field[3],
                'value' => 'null',
                'atributos' => '{"class":"form-control"}',
                'definicion' => '',
                'requerido' => $field[4],
                'editable' => 1,
                'unico' => 0,
                'updated_at' => $now,
            );

            if ($fieldId === 0) {
                $data['created_at'] = $now;
                $fieldId = (int)DB::table('sys_campos')->insertGetId($data);
            } else {
                DB::table('sys_campos')->where('id', $fieldId)->update($data);
            }

            $relation = DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $reportId)
                ->where('core_campo_id', $fieldId);

            if ($relation->exists()) {
                $relation->update(array('orden' => $index + 1));
            } else {
                DB::table('sys_reporte_tiene_campos')->insert(array(
                    'orden' => $index + 1,
                    'core_reporte_id' => $reportId,
                    'core_campo_id' => $fieldId,
                ));
            }
        }
    }

    protected function registerPermission($appId, $now)
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permissionId = (int)DB::table('permissions')->where('name', $this->reportUrl)->value('id');
        $data = array(
            'core_app_id' => $appId,
            'modelo_id' => 0,
            'name' => $this->reportUrl,
            'descripcion' => 'Reporte listado de cheques',
            'url' => 'web',
            'parent' => 0,
            'orden' => 99,
            'enabled' => 0,
            'fa_icon' => 'file-text-o',
            'updated_at' => $now,
        );

        if (Schema::hasColumn('permissions', 'guard_name')) {
            $data['guard_name'] = 'web';
        }

        if ($permissionId === 0) {
            $data['created_at'] = $now;
            $permissionId = (int)DB::table('permissions')->insertGetId($data);
        } else {
            DB::table('permissions')->where('id', $permissionId)->update($data);
        }

        if (!Schema::hasTable('roles') || !Schema::hasTable('role_has_permissions')) {
            return;
        }

        $roleIds = DB::table('roles')
            ->whereIn('name', array('SuperAdmin', 'Administrador'))
            ->lists('id');

        // Los perfiles que ya consultan Movimiento de Caja/Bancos reciben el
        // nuevo reporte de cheques sin requerir configuración manual adicional.
        $basePermissionId = (int)DB::table('permissions')
            ->where('name', 'teso_movimiento_caja_bancos')
            ->value('id');
        if ($basePermissionId > 0) {
            $roleIds = array_merge(
                $roleIds,
                DB::table('role_has_permissions')
                    ->where('permission_id', $basePermissionId)
                    ->lists('role_id')
            );
        }

        foreach (array_unique(array_map('intval', $roleIds)) as $roleId) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->exists();
            if (!$exists) {
                $pivot = array('role_id' => $roleId, 'permission_id' => $permissionId);
                if (Schema::hasColumn('role_has_permissions', 'orden')) {
                    $pivot['orden'] = 0;
                }
                DB::table('role_has_permissions')->insert($pivot);
            }
        }
    }

    protected function forgetPermissionCache()
    {
        if (class_exists('Spatie\\Permission\\PermissionRegistrar')) {
            app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
