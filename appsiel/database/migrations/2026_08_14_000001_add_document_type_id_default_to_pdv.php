<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddDocumentTypeIdDefaultToPdv extends Migration
{
    protected $fieldName = 'document_type_id_default';
    protected $modelNamespace = 'App\\VentasPos\\Pdv';

    public function up()
    {
        if (Schema::hasTable('vtas_pos_puntos_de_ventas') && !Schema::hasColumn('vtas_pos_puntos_de_ventas', $this->fieldName)) {
            Schema::table('vtas_pos_puntos_de_ventas', function (Blueprint $table) {
                $table->unsignedInteger($this->fieldName)->nullable()->after('tipo_doc_app_default_id');
            });
        }

        $this->registerCrudField();
    }

    public function down()
    {
        $this->removeCrudField();

        if (Schema::hasTable('vtas_pos_puntos_de_ventas') && Schema::hasColumn('vtas_pos_puntos_de_ventas', $this->fieldName)) {
            Schema::table('vtas_pos_puntos_de_ventas', function (Blueprint $table) {
                $table->dropColumn($this->fieldName);
            });
        }
    }

    protected function registerCrudField()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');
        if (!$modeloId) {
            return;
        }

        $campoId = DB::table('sys_campos')->where('name', $this->fieldName)->value('id');
        if (!$campoId) {
            $now = date('Y-m-d H:i:s');
            $campoId = DB::table('sys_campos')->insertGetId([
                'descripcion' => 'Tipo documento FE (vacío usa configuración general)',
                'tipo' => 'select',
                'name' => $this->fieldName,
                'opciones' => 'model_App\\Core\\TipoDocApp',
                'value' => 'null',
                'atributos' => '{"class":"combobox"}',
                'definicion' => '',
                'requerido' => 0,
                'editable' => 1,
                'unico' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $relationExists = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modeloId)
            ->where('core_campo_id', $campoId)
            ->exists();

        if (!$relationExists) {
            DB::table('sys_modelo_tiene_campos')->insert([
                'orden' => 20,
                'core_modelo_id' => $modeloId,
                'core_campo_id' => $campoId,
            ]);
        }
    }

    protected function removeCrudField()
    {
        if (!Schema::hasTable('sys_campos')) {
            return;
        }

        $campoId = DB::table('sys_campos')->where('name', $this->fieldName)->value('id');
        if (!$campoId) {
            return;
        }

        if (Schema::hasTable('sys_modelo_tiene_campos')) {
            $modeloId = Schema::hasTable('sys_modelos')
                ? DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id')
                : null;

            if ($modeloId) {
                DB::table('sys_modelo_tiene_campos')
                    ->where('core_modelo_id', $modeloId)
                    ->where('core_campo_id', $campoId)
                    ->delete();
            }

            if (DB::table('sys_modelo_tiene_campos')->where('core_campo_id', $campoId)->exists()) {
                return;
            }
        }

        DB::table('sys_campos')->where('id', $campoId)->delete();
    }
}
