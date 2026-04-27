<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateInvFisicoDescontarVentasPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 0;
        if (DB::getSchemaBuilder()->hasTable('sys_aplicaciones')) {
            $coreAppId = (int) DB::table('sys_aplicaciones')
                ->where('descripcion', 'Inventarios')
                ->value('id');
        }

        $permission = Permission::firstOrNew([
            'name' => 'inventarios.inventario_fisico.descontar_ventas'
        ]);

        $permission->core_app_id = $coreAppId;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Descontar ventas desde Inventario Fisico';
        $permission->url = 'inv_fisico_descontar_ventas';
        $permission->parent = 0;
        $permission->orden = 0;
        $permission->enabled = 0;
        $permission->fa_icon = 'fa fa-cutlery';
        $permission->save();

        $roles = [
            'SuperAdmin',
            'Administrador'
        ];

        foreach ($roles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->givePermissionTo($permission);
        }
    }

    public function down()
    {
        $permission = Permission::where('name', 'inventarios.inventario_fisico.descontar_ventas')->first();
        if (!is_null($permission)) {
            $permission->delete();
        }
    }
}
