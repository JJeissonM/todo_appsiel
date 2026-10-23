<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterGestionDocumentalPazYSalvoReport extends Migration
{
    protected $reportUrl = 'gestion_documental/paz_y_salvo';

    public function up()
    {
        if (!Schema::hasTable('sys_aplicaciones') || !Schema::hasTable('sys_reportes')) {
            return;
        }

        $appId = (int)DB::table('sys_aplicaciones')
            ->where(function ($query) {
                $query->where('app', 'gestion_documental');
            })
            ->value('id');

        if ($appId === 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $reportId = (int)DB::table('sys_reportes')->where('url_form_action', $this->reportUrl)->value('id');
        $reportData = array(
            'descripcion' => 'Paz y Salvo',
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
        $reportId = 0;
        if (Schema::hasTable('sys_reportes')) {
            $reportId = (int)DB::table('sys_reportes')->where('url_form_action', $this->reportUrl)->value('id');
        }

        if ($reportId > 0 && Schema::hasTable('sys_reporte_tiene_campos')) {
            DB::table('sys_reporte_tiene_campos')->where('core_reporte_id', $reportId)->delete();
        }

        if ($reportId > 0) {
            DB::table('sys_reportes')->where('id', $reportId)->delete();
        }

        if (Schema::hasTable('sys_campos') && Schema::hasTable('sys_reporte_tiene_campos')) {
            $fields = DB::table('sys_campos')->where('definicion', $this->reportUrl)->get();
            foreach ($fields as $field) {
                if (!DB::table('sys_reporte_tiene_campos')->where('core_campo_id', $field->id)->exists()) {
                    DB::table('sys_campos')->where('id', $field->id)->delete();
                }
            }
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
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_reporte_tiene_campos')) {
            return;
        }

        // Identificador propio en definicion; los nombres permiten reutilizar los filtros dependientes.
        $fields = array(
            array('Año lectivo', 'select', 'periodo_lectivo_id', 'model_App\\Matriculas\\PeriodoLectivoAux', 'null', 1),
            array('Curso', 'select', 'curso_id', '{"":""}', 'null', 1),
            array('Estudiante', 'select', 'estudiante_id', '{"":""}', 'null', 1),
            array('Fecha de expedición', 'fecha', 'fecha_expedicion', '', 'null', 1),
            array('Firma autorizada', 'select', 'firma_autorizada_1', 'model_App\\Core\\FirmaAutorizada', 'null', 1),
        );

        $order = 1;
        foreach ($fields as $field) {
            $fieldId = (int)DB::table('sys_campos')
                ->where('definicion', $this->reportUrl)
                ->where('name', $field[2])
                ->where('tipo', $field[1])
                ->value('id');

            $data = array(
                'descripcion' => $field[0],
                'tipo' => $field[1],
                'name' => $field[2],
                'opciones' => $field[3],
                'value' => $field[4],
                'atributos' => '{"class":"form-control"}',
                'definicion' => $this->reportUrl,
                'requerido' => $field[5],
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
                $relation->update(array('orden' => $order));
            } else {
                DB::table('sys_reporte_tiene_campos')->insert(array(
                    'orden' => $order,
                    'core_reporte_id' => $reportId,
                    'core_campo_id' => $fieldId,
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
            'descripcion' => 'Paz y Salvo',
            'url' => 'web',
            'parent' => 0,
            'orden' => 99,
            'enabled' => 0,
            'fa_icon' => 'bar-chart',
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

        $roles = DB::table('roles')->whereIn('name', array('SuperAdmin', 'Administrador'))->get();
        foreach ($roles as $role) {
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $role->id)
                ->where('permission_id', $permissionId)
                ->exists();

            if ($exists) {
                continue;
            }

            $pivot = array('role_id' => $role->id, 'permission_id' => $permissionId);
            if (Schema::hasColumn('role_has_permissions', 'orden')) {
                $pivot['orden'] = 0;
            }
            DB::table('role_has_permissions')->insert($pivot);
        }
    }

    protected function forgetPermissionCache()
    {
        if (class_exists(Spatie\Permission\PermissionRegistrar::class)) {
            app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
