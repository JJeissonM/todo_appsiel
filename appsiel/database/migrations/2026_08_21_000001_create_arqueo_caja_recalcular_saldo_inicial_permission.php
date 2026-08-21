<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

class CreateArqueoCajaRecalcularSaldoInicialPermission extends Migration
{
    const PERMISSION_NAME = 'teso_arqueo_caja_recalcular_saldo_inicial';

    public function up()
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $permission_id = DB::table('permissions')->where('name', self::PERMISSION_NAME)->value('id');
        $data = [
            'core_app_id' => 3,
            'modelo_id' => 158,
            'descripcion' => 'Recalcular saldo inicial en Arqueo de Caja',
            'url' => 'web',
            'parent' => 0,
            'orden' => 1,
            'enabled' => 0,
            'fa_icon' => 'refresh',
            'updated_at' => $now
        ];

        if (!$permission_id) {
            $data['name'] = self::PERMISSION_NAME;
            $data['created_at'] = $now;

            if (Schema::hasColumn('permissions', 'guard_name')) {
                $data['guard_name'] = 'web';
            }

            DB::table('permissions')->insert($data);
        } else {
            DB::table('permissions')->where('id', $permission_id)->update($data);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', self::PERMISSION_NAME)->delete();
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }
}
