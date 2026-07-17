<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvBodegaIdToVtasPosDocRegistros extends Migration
{
    public function up()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('vtas_pos_doc_registros') || Schema::hasColumn('vtas_pos_doc_registros', 'inv_bodega_id')) {
            return;
        }

        Schema::table('vtas_pos_doc_registros', function (Blueprint $table) {
            $table->integer('inv_bodega_id')->unsigned()->nullable()->after('inv_producto_id');
            $table->index('inv_bodega_id');
        });
    }

    public function down()
    {
        if (!$this->hotelModuleEnabled() || !Schema::hasTable('vtas_pos_doc_registros') || !Schema::hasColumn('vtas_pos_doc_registros', 'inv_bodega_id')) {
            return;
        }

        Schema::table('vtas_pos_doc_registros', function (Blueprint $table) {
            $table->dropIndex(array('inv_bodega_id'));
            $table->dropColumn('inv_bodega_id');
        });
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
