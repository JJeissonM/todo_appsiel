<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EnsureInvBodegaIdNotNullOnComprasProveedores extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('compras_proveedores') || !Schema::hasColumn('compras_proveedores', 'inv_bodega_id')) {
            return;
        }

        $defaultInvBodegaId = (int) config('inventarios.item_bodega_principal_id');
        if ($defaultInvBodegaId <= 0) {
            $defaultInvBodegaId = 1;
        }

        DB::table('compras_proveedores')
            ->whereNull('inv_bodega_id')
            ->update(['inv_bodega_id' => $defaultInvBodegaId]);

        DB::statement('ALTER TABLE compras_proveedores MODIFY inv_bodega_id INT(10) UNSIGNED NOT NULL');
    }

    public function down()
    {
        if (!Schema::hasTable('compras_proveedores') || !Schema::hasColumn('compras_proveedores', 'inv_bodega_id')) {
            return;
        }

        DB::statement('ALTER TABLE compras_proveedores MODIFY inv_bodega_id INT(10) UNSIGNED NULL');
    }
}
