<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AcademicInclusionFieldsSeeder extends Seeder
{
    public function run()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $this->addFieldToModel('App\\Matriculas\\Inscripcion', 'Estudiante de inclusión', 'es_de_inclusion', 16);
        $this->addFieldToModel('App\\Matriculas\\Inscripcion', 'Diagnóstico de inclusión', 'diagnostico_inclusion', 17, 'bsTextArea', '', '');
        $this->addFieldToModel('App\\Matriculas\\InscripcionEnLinea', 'Estudiante de inclusión', 'es_de_inclusion', 16);
        $this->addFieldToModel('App\\Matriculas\\InscripcionEnLinea', 'Diagnóstico de inclusión', 'diagnostico_inclusion', 17, 'bsTextArea', '', '');
        $this->addFieldToModel('App\\Matriculas\\Estudiante', 'Estudiante de inclusión', 'es_de_inclusion', 18);
        $this->addFieldToModel('App\\Matriculas\\Estudiante', 'Diagnóstico de inclusión', 'diagnostico_inclusion', 19, 'bsTextArea', '', '');
        $this->addFieldToModel('App\\Matriculas\\Matricula', 'Estudiante de inclusión', 'es_de_inclusion', 10);
        $this->addFieldToModel('App\\Matriculas\\Matricula', 'Diagnóstico de inclusión', 'diagnostico_inclusion', 11, 'bsTextArea', '', '');
        $this->addFieldToModel('App\\Calificaciones\\Meta', 'Propósito para inclusión', 'es_para_inclusion', 7);
    }

    private function addFieldToModel($modelNamespace, $label, $fieldName, $order, $type = 'select', $options = '{"0":"No","1":"Si"}', $value = '0')
    {
        $modelId = (int)DB::table('sys_modelos')->where('name_space', $modelNamespace)->value('id');

        if ($modelId == 0) {
            return;
        }

        $fieldId = $this->getOrCreateField($label, $fieldName, $type, $options, $value);

        $relation = DB::table('sys_modelo_tiene_campos')
            ->where('core_modelo_id', $modelId)
            ->where('core_campo_id', $fieldId);

        if ($relation->exists()) {
            $relation->update(['orden' => $order]);
            return;
        }

        DB::table('sys_modelo_tiene_campos')->insert([
            'orden' => $order,
            'core_modelo_id' => $modelId,
            'core_campo_id' => $fieldId,
        ]);
    }

    private function getOrCreateField($label, $fieldName, $type, $options, $value)
    {
        $fieldId = (int)DB::table('sys_campos')->where('name', $fieldName)->value('id');

        $data = [
            'descripcion' => $label,
            'tipo' => $type,
            'name' => $fieldName,
            'opciones' => $options,
            'value' => $value,
            'atributos' => '',
            'definicion' => '',
            'requerido' => 0,
            'editable' => 1,
            'unico' => 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($fieldId > 0) {
            DB::table('sys_campos')->where('id', $fieldId)->update($data);
            return $fieldId;
        }

        $data['created_at'] = date('Y-m-d H:i:s');

        return (int)DB::table('sys_campos')->insertGetId($data);
    }
}
