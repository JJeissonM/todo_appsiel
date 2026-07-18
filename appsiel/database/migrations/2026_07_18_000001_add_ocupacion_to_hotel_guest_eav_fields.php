<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddOcupacionToHotelGuestEavFields extends Migration
{
    protected $fieldDescription = 'Ocupación';
    protected $legacyFieldName = 'hotel_guest_ocupacion';
    protected $modelNamespace = 'App\\Hotel\\HotelGuest';

    public function up()
    {
        if (!$this->hotelModuleEnabled()) {
            return;
        }

        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modelId = (int)DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');
        if ($modelId == 0) {
            return;
        }

        $fieldId = $this->getOrCreateField();
        if ($fieldId == 0) {
            return;
        }

        $exists = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modelId)
            ->where('core_campo_id', $fieldId)
            ->exists();

        if ($exists) {
            DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modelId)
                ->where('core_campo_id', $fieldId)
                ->update(array('orden' => 104));
            return;
        }

        DB::table('sys_modelo_tiene_campos')->insert(array(
            'orden' => 104,
            'core_modelo_id' => $modelId,
            'core_campo_id' => $fieldId,
        ));
    }

    public function down()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        $fieldId = (int)DB::table('sys_campos')
            ->where('name', 'core_campo_id-ID')
            ->where('descripcion', $this->fieldDescription)
            ->value('id');

        if ($fieldId == 0) {
            $fieldId = (int)DB::table('sys_campos')->where('name', $this->legacyFieldName)->value('id');
        }

        if ($fieldId == 0) {
            return;
        }

        if (Schema::hasTable('sys_modelos') && Schema::hasTable('sys_modelo_tiene_campos')) {
            $modelId = (int)DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');

            if ($modelId > 0) {
                DB::table('sys_modelo_tiene_campos')
                    ->where('core_modelo_id', $modelId)
                    ->where('core_campo_id', $fieldId)
                    ->delete();
            }

            if (DB::table('sys_modelo_tiene_campos')->where('core_campo_id', $fieldId)->exists()) {
                return;
            }
        }

        DB::table('sys_campos')->where('id', $fieldId)->delete();
    }

    protected function getOrCreateField()
    {
        $fieldId = (int)DB::table('sys_campos')
            ->where('name', 'core_campo_id-ID')
            ->where('descripcion', $this->fieldDescription)
            ->value('id');

        $now = date('Y-m-d H:i:s');
        $data = array(
            'descripcion' => $this->fieldDescription,
            'tipo' => 'bsText',
            'name' => 'core_campo_id-ID',
            'opciones' => '',
            'value' => '',
            'atributos' => '{"class":"form-control"}',
            'definicion' => '',
            'requerido' => 0,
            'editable' => 1,
            'unico' => 0,
            'updated_at' => $now,
        );

        $data = $this->onlyExistingColumns('sys_campos', $data);

        if ($fieldId > 0) {
            DB::table('sys_campos')->where('id', $fieldId)->update($data);
            return $fieldId;
        }

        $legacyFieldId = (int)DB::table('sys_campos')->where('name', $this->legacyFieldName)->value('id');
        if ($legacyFieldId > 0) {
            DB::table('sys_campos')->where('id', $legacyFieldId)->update($data);
            return $legacyFieldId;
        }

        $data['created_at'] = $now;
        $data = $this->onlyExistingColumns('sys_campos', $data);

        return (int)DB::table('sys_campos')->insertGetId($data);
    }

    protected function onlyExistingColumns($table, $data)
    {
        foreach (array_keys($data) as $column) {
            if (!Schema::hasColumn($table, $column)) {
                unset($data[$column]);
            }
        }

        return $data;
    }

    protected function hotelModuleEnabled()
    {
        return filter_var(env('HOTEL_MODULE_ENABLED', env('HOTEL_MODULE_SEEDERS_ENABLED', false)), FILTER_VALIDATE_BOOLEAN);
    }
}
