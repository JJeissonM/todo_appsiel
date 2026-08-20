<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterSalesCxcMovementsReport extends Migration
{
    protected $reportUrl = 'vtas_movimientos_de_cxc';

    public function up()
    {
        if (!Schema::hasTable('sys_aplicaciones') ||
            !Schema::hasTable('sys_reportes') ||
            !Schema::hasTable('sys_campos') ||
            !Schema::hasTable('sys_reporte_tiene_campos')) {
            return;
        }

        $appId = (int)DB::table('sys_aplicaciones')->where('app', 'ventas')->value('id');
        if ($appId === 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $reportId = (int)DB::table('sys_reportes')
            ->where('url_form_action', $this->reportUrl)
            ->value('id');
        $reportData = array(
            'descripcion' => 'Movimientos de CxC',
            'core_app_id' => $appId,
            'url_form_action' => $this->reportUrl,
            'estado' => 'Activo',
            'updated_at' => $now
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
        $reportId = 0;
        if (Schema::hasTable('sys_reportes')) {
            $reportId = (int)DB::table('sys_reportes')
                ->where('url_form_action', $this->reportUrl)
                ->value('id');
        }

        if ($reportId > 0 && Schema::hasTable('sys_reporte_tiene_campos')) {
            DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $reportId)->delete();
        }
        if ($reportId > 0) {
            DB::table('sys_reportes')->where('id', $reportId)->delete();
        }

        if (Schema::hasTable('permissions')) {
            $permissionId = (int)DB::table('permissions')->where('name', $this->reportUrl)->value('id');
            if ($permissionId > 0) {
                if (Schema::hasTable('role_has_permissions')) {
                    DB::table('role_has_permissions')->where('permission_id', $permissionId)->delete();
                }
                DB::table('permissions')->where('id', $permissionId)->delete();
            }
        }

        $this->forgetPermissionCache();
    }

    protected function registerFields($reportId, $now)
    {
        $fields = array(
            array(
                'descripcion' => 'Fecha desde',
                'tipo' => 'date',
                'name' => 'fecha_desde',
                'opciones' => '',
                'value' => 'null'
            ),
            array(
                'descripcion' => 'Fecha hasta',
                'tipo' => 'date',
                'name' => 'fecha_hasta',
                'opciones' => '',
                'value' => 'null'
            ),
            array(
                'descripcion' => 'Tercero',
                'tipo' => 'select',
                'name' => 'core_tercero_id',
                'opciones' => 'model_App\\Core\\Tercero',
                'value' => 'null'
            )
        );

        $order = 1;
        foreach ($fields as $field) {
            $query = DB::table('sys_campos')
                ->where('name', $field['name'])
                ->where('tipo', $field['tipo']);

            if ($field['name'] === 'core_tercero_id') {
                $query->where('opciones', $field['opciones']);
            }

            $fieldId = (int)$query->value('id');
            if ($fieldId === 0) {
                $fieldId = (int)DB::table('sys_campos')->insertGetId(array(
                    'descripcion' => $field['descripcion'],
                    'tipo' => $field['tipo'],
                    'name' => $field['name'],
                    'opciones' => $field['opciones'],
                    'value' => $field['value'],
                    'atributos' => '{"class":"form-control"}',
                    'definicion' => '',
                    'requerido' => 1,
                    'editable' => 1,
                    'unico' => 0,
                    'created_at' => $now,
                    'updated_at' => $now
                ));
            }

            $relation = DB::table('sys_reporte_tiene_campos')
                ->where('core_reporte_id', $reportId)
                ->where('core_campo_id', $fieldId);

            if ($relation->exists()) {
                $relation->update(array('orden' => $order));
            } else {
                DB::table('sys_reporte_tiene_campos')->insert(array(
                    'orden' => $order,
                    'core_reporte_id' => $reportId,
                    'core_campo_id' => $fieldId
                ));
            }

            $order++;
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
            'descripcion' => 'Ver reporte Ventas > Movimientos de CxC',
            'url' => 'web',
            'parent' => 0,
            'orden' => 99,
            'enabled' => 0,
            'fa_icon' => 'list-alt',
            'updated_at' => $now
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

        $roles = DB::table('roles')->whereIn('name', array('SuperAdmin', 'Administrador'))->get();
        foreach ($roles as $role) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if (!$exists) {
                $pivot = array('role_id' => $role->id, 'permission_id' => $permissionId);
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
