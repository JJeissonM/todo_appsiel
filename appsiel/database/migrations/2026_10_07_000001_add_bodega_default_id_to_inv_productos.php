<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBodegaDefaultIdToInvProductos extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('inv_productos', 'bodega_default_id')) {
            Schema::table('inv_productos', function (Blueprint $table) {
                $table->unsignedInteger('bodega_default_id')->nullable();
            });
        }
        if (!$this->hasCrudTables()) {
            return;
        }
        $modelId = $this->productModelId();
        if (!$modelId || $this->fieldIds($modelId)) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        // Campo propio del catálogo: no modifica la obligatoriedad de otros formularios.
        $fieldId = DB::table('sys_campos')->insertGetId([
            'descripcion' => 'Bodega por defecto (consumo en ensambles)',
            'tipo' => 'select', 'name' => 'bodega_default_id',
            'opciones' => 'model_App\\Inventarios\\InvBodega', 'value' => 'null',
            'atributos' => '{"class":"combobox"}', 'definicion' => '',
            'requerido' => 0, 'editable' => 1, 'unico' => 0,
            'created_at' => $now, 'updated_at' => $now
        ]);
        $order = (int)DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $modelId)->max('orden') + 1;
        DB::table('sys_modelo_tiene_campos')->insert([
            'core_modelo_id' => $modelId, 'core_campo_id' => $fieldId, 'orden' => $order
        ]);
    }

    public function down()
    {
        if ($this->hasCrudTables()) {
            $modelId = $this->productModelId();
            foreach ($this->fieldIds($modelId) as $fieldId) {
                DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $modelId)->where('core_campo_id', $fieldId)->delete();
                if (!DB::table('sys_modelo_tiene_campos')->where('core_campo_id', $fieldId)->exists()) {
                    DB::table('sys_campos')->where('id', $fieldId)->delete();
                }
            }
        }
        if (Schema::hasColumn('inv_productos', 'bodega_default_id')) {
            Schema::table('inv_productos', function (Blueprint $table) {
                $table->dropColumn('bodega_default_id');
            });
        }
    }

    protected function hasCrudTables()
    {
        return Schema::hasTable('sys_modelos') && Schema::hasTable('sys_campos') && Schema::hasTable('sys_modelo_tiene_campos');
    }

    protected function productModelId()
    {
        return DB::table('sys_modelos')->where('name_space', 'App\\Inventarios\\InvProducto')->value('id');
    }

    protected function fieldIds($modelId)
    {
        return DB::table('sys_modelo_tiene_campos AS relation')
            ->join('sys_campos AS field', 'field.id', '=', 'relation.core_campo_id')
            ->where('relation.core_modelo_id', $modelId)->where('field.name', 'bodega_default_id')
            ->pluck('field.id');
    }
}
