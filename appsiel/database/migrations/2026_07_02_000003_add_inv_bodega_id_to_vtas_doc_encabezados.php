<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddInvBodegaIdToVtasDocEncabezados extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('vtas_doc_encabezados') || Schema::hasColumn('vtas_doc_encabezados', 'inv_bodega_id')) {
            return;
        }

        $defaultInvBodegaId = $this->getDefaultInvBodegaId();

        Schema::table('vtas_doc_encabezados', function (Blueprint $table) {
            $table->integer('inv_bodega_id')->unsigned()->nullable()->after('vendedor_id');
            $table->index('inv_bodega_id');
        });

        DB::statement(
            'UPDATE vtas_doc_encabezados vde ' .
            'LEFT JOIN vtas_clientes vc ON vc.id = vde.cliente_id ' .
            'SET vde.inv_bodega_id = COALESCE(NULLIF(vc.inv_bodega_id, 0), ' . $defaultInvBodegaId . ') ' .
            'WHERE vde.inv_bodega_id IS NULL OR vde.inv_bodega_id = 0'
        );

        DB::statement('ALTER TABLE vtas_doc_encabezados MODIFY inv_bodega_id INT(10) UNSIGNED NOT NULL DEFAULT ' . $defaultInvBodegaId);
    }

    public function down()
    {
        if (!Schema::hasTable('vtas_doc_encabezados') || !Schema::hasColumn('vtas_doc_encabezados', 'inv_bodega_id')) {
            return;
        }

        Schema::table('vtas_doc_encabezados', function (Blueprint $table) {
            $table->dropIndex(['inv_bodega_id']);
            $table->dropColumn('inv_bodega_id');
        });
    }

    protected function getDefaultInvBodegaId()
    {
        $defaultInvBodegaId = (int) config('ventas.inv_bodega_id');

        if ($defaultInvBodegaId <= 0) {
            $defaultInvBodegaId = (int) config('inventarios.item_bodega_principal_id');
        }

        if ($defaultInvBodegaId <= 0 && Schema::hasTable('inv_bodegas')) {
            $defaultInvBodegaId = (int) DB::table('inv_bodegas')->value('id');
        }

        if ($defaultInvBodegaId <= 0) {
            $defaultInvBodegaId = 1;
        }

        return $defaultInvBodegaId;
    }
}
