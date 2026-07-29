<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddResponsableToVtasPosAperturaEncabezados extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vtas_pos_apertura_encabezados') && !Schema::hasColumn('vtas_pos_apertura_encabezados', 'responsable')) {
            Schema::table('vtas_pos_apertura_encabezados', function (Blueprint $table) {
                $table->string('responsable', 100)->nullable()->after('efectivo_base');
            });
        }

        $this->registerCrudField();
    }

    public function down()
    {
        $this->removeCrudField();

        if (Schema::hasTable('vtas_pos_apertura_encabezados') && Schema::hasColumn('vtas_pos_apertura_encabezados', 'responsable')) {
            Schema::table('vtas_pos_apertura_encabezados', function (Blueprint $table) {
                $table->dropColumn('responsable');
            });
        }
    }

    private function registerCrudField()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\VentasPos\\AperturaEncabezado')
            ->value('id');

        if ($modeloId == 0) {
            return;
        }

        $campoId = (int)DB::table('sys_campos')
            ->where('name', 'responsable')
            ->value('id');

        if ($campoId == 0) {
            $campoId = DB::table('sys_campos')->insertGetId($this->onlyExistingColumns('sys_campos', array(
                'descripcion' => 'Responsable',
                'tipo' => 'bsText',
                'name' => 'responsable',
                'opciones' => '',
                'value' => '',
                'atributos' => '{"class":"form-control"}',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            )));
        }

        $exists = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modeloId)
            ->where('core_campo_id', $campoId)
            ->exists();

        if (!$exists) {
            $relation = array(
                'core_modelo_id' => $modeloId,
                'core_campo_id' => $campoId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            );

            if (Schema::hasColumn('sys_modelo_tiene_campos', 'orden')) {
                $relation['orden'] = (int)DB::table('sys_modelo_tiene_campos')
                    ->where('core_modelo_id', $modeloId)
                    ->max('orden') + 1;
            }

            DB::table('sys_modelo_tiene_campos')->insert($this->onlyExistingColumns('sys_modelo_tiene_campos', $relation));
        }
    }

    private function removeCrudField()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = (int)DB::table('sys_modelos')
            ->where('name_space', 'App\\VentasPos\\AperturaEncabezado')
            ->value('id');

        $campoId = (int)DB::table('sys_campos')
            ->where('name', 'responsable')
            ->value('id');

        if ($modeloId == 0 || $campoId == 0) {
            return;
        }

        DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modeloId)
            ->where('core_campo_id', $campoId)
            ->delete();
    }

    private function onlyExistingColumns($table, array $data)
    {
        $filtered = array();

        foreach ($data as $key => $value) {
            if (Schema::hasColumn($table, $key)) {
                $filtered[$key] = $value;
            }
        }

        return $filtered;
    }
}
