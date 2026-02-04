<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateFuecAnulacionPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 0;
        if (DB::getSchemaBuilder()->hasTable('sys_aplicaciones')) {
            $coreAppId = (int) DB::table('sys_aplicaciones')
                ->where('descripcion', 'Contratos transporte')
                ->value('id');
        }

        $permission = Permission::firstOrNew([
            'name' => 'cte_fuec.anular'
        ]);

        $permission->core_app_id = $coreAppId;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Anular FUEC';
        $permission->url = '';
        $permission->parent = 0;
        $permission->orden = 0;
        $permission->enabled = 0;
        $permission->fa_icon = 'fa fa-close';
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
        $permission = Permission::where('name', 'cte_fuec.anular')->first();
        if (!is_null($permission)) {
            $permission->delete();
        }
    }
}
