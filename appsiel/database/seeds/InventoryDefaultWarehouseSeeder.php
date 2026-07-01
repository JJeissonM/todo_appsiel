<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryDefaultWarehouseSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('inv_bodegas')) {
            return;
        }

        $empresaId = $this->getEmpresaId();
        $defaultId = (int)config('inventarios.item_bodega_principal_id');

        $defaultWarehouse = null;
        if ($defaultId > 0) {
            $defaultWarehouse = DB::table('inv_bodegas')->where('id', $defaultId)->first();
        }

        if (!is_null($defaultWarehouse) && (int)$defaultWarehouse->core_empresa_id == $empresaId) {
            DB::table('inv_bodegas')
                ->where('id', $defaultId)
                ->update($this->timestamps([
                    'estado' => 'Activo',
                ], false));

            return;
        }

        $activeWarehouse = DB::table('inv_bodegas')
            ->where('core_empresa_id', $empresaId)
            ->where('estado', 'Activo')
            ->orderBy('id')
            ->first();

        if (!is_null($activeWarehouse)) {
            return;
        }

        $data = [
            'core_empresa_id' => $empresaId,
            'descripcion' => 'Bodega principal',
            'estado' => 'Activo',
        ];

        if ($defaultId > 0 && is_null($defaultWarehouse)) {
            $data['id'] = $defaultId;
        }

        DB::table('inv_bodegas')->insert($this->timestamps($data, true));
    }

    protected function getEmpresaId()
    {
        if (Schema::hasTable('core_empresas')) {
            $empresaId = (int)DB::table('core_empresas')->orderBy('id')->value('id');
            if ($empresaId > 0) {
                return $empresaId;
            }
        }

        return 1;
    }

    protected function timestamps(array $data, $creating)
    {
        $now = date('Y-m-d H:i:s');

        if ($creating && Schema::hasColumn('inv_bodegas', 'created_at')) {
            $data['created_at'] = $now;
        }

        if (Schema::hasColumn('inv_bodegas', 'updated_at')) {
            $data['updated_at'] = $now;
        }

        return $data;
    }
}
