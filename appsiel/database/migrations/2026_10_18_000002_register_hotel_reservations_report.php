<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RegisterHotelReservationsReport extends Migration
{
    protected $reportUrl = 'hotel/reports/reservations';

    public function up()
    {
        if (!Schema::hasTable('sys_aplicaciones') || !Schema::hasTable('sys_reportes')) {
            return;
        }

        $appId = (int)DB::table('sys_aplicaciones')
            ->where(function ($query) {
                $query->where('app', 'hotel')
                    ->orWhere('descripcion', 'Gestión Hotelera')
                    ->orWhere('descripcion', 'Gestion Hotelera');
            })
            ->value('id');

        if ($appId === 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $reportId = (int)DB::table('sys_reportes')->where('url_form_action', $this->reportUrl)->value('id');
        $reportData = array(
            'descripcion' => 'Reservas',
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
            foreach (array('hotel_reservations_desde', 'hotel_reservations_hasta') as $name) {
                $fieldId = DB::table('sys_campos')->where('name', $name)->value('id');
                if ($fieldId && !DB::table('sys_reporte_tiene_campos')->where('core_campo_id', $fieldId)->exists()) {
                    DB::table('sys_campos')->where('id', $fieldId)->delete();
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

        // Campos propios para no cambiar la obligatoriedad de otros reportes.
        $fields = array(
            array('Fecha desde', 'date', 'hotel_reservations_desde', '', 'null', 0),
            array('Fecha hasta', 'date', 'hotel_reservations_hasta', '', 'null', 0),
        );

        $order = 1;
        foreach ($fields as $field) {
            $fieldId = (int)DB::table('sys_campos')
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
                'definicion' => '',
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
            'descripcion' => 'Reporte de reservas',
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
        if (class_exists('Spatie\\Permission\\PermissionRegistrar')) {
            app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
