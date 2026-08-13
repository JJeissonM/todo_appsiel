<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

class CreateArqueoCajaMovimientosPermission extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $permission = Permission::firstOrNew([
            'name' => 'vtas_pos_bloqueo_ver_movimientos_sistema_en_arqueo_caja'
        ]);

        $permission->core_app_id = 20;
        $permission->modelo_id = 0;
        $permission->descripcion = 'Bloqueado para ver movimientos del sistema en Arqueo de Caja';
        $permission->url = 'web';
        $permission->parent = 0;
        $permission->orden = 1;
        $permission->enabled = 0;
        $permission->fa_icon = '';
        $permission->save();
    }

    public function down()
    {
        $permission = Permission::where(
            'name',
            'vtas_pos_bloqueo_ver_movimientos_sistema_en_arqueo_caja'
        )->first();

        if (!is_null($permission)) {
            $permission->delete();
        }
    }
}
