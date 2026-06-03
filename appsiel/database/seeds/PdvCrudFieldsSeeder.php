<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PdvCrudFieldsSeeder extends Seeder
{
    protected $modelNamespace = 'App\\VentasPos\\Pdv';

    public function run()
    {
        if (!Schema::hasTable('sys_modelos') || !Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $modeloId = (int)DB::table('sys_modelos')
            ->where('name_space', $this->modelNamespace)
            ->value('id');

        if ($modeloId == 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $campos = $this->getCampos();

        foreach ($campos as $campo) {
            $orden = $campo['orden'];
            unset($campo['orden']);

            $campoRelacionadoId = $this->getCampoRelacionadoId($modeloId, $campo['name']);

            if ($campoRelacionadoId) {
                if ($this->shouldUpdateExistingField($campo['name'])) {
                    $campo['updated_at'] = $now;
                    DB::table('sys_campos')->where('id', $campoRelacionadoId)->update($campo);
                }

                DB::table('sys_modelo_tiene_campos')
                    ->where('core_modelo_id', $modeloId)
                    ->where('core_campo_id', $campoRelacionadoId)
                    ->update(['orden' => $orden]);

                continue;
            }

            $campoId = DB::table('sys_campos')->where('name', $campo['name'])->value('id');

            if (!$campoId) {
                $campo['created_at'] = $now;
                $campo['updated_at'] = $now;
                $campoId = DB::table('sys_campos')->insertGetId($campo);
            } elseif ($this->shouldUpdateExistingField($campo['name'])) {
                $campo['updated_at'] = $now;
                DB::table('sys_campos')->where('id', $campoId)->update($campo);
            }

            $relacion = DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modeloId)
                ->where('core_campo_id', $campoId);

            if ($relacion->exists()) {
                $relacion->update(['orden' => $orden]);
                continue;
            }

            DB::table('sys_modelo_tiene_campos')->insert([
                'orden' => $orden,
                'core_modelo_id' => $modeloId,
                'core_campo_id' => $campoId,
            ]);
        }

        $this->removeDuplicateRelations($modeloId);
    }

    protected function getCampos()
    {
        return [
            $this->campo(1, 'Descripcion', 'bsText', 'descripcion', '', 'null', '', 1),
            $this->campo(2, 'Bodega por defecto', 'select', 'bodega_default_id', 'model_App\\Inventarios\\InvBodega', 'null', '{"class":"combobox"}', 1),
            $this->campo(3, 'Caja por defecto', 'select', 'caja_default_id', 'model_App\\Tesoreria\\TesoCaja', 'null', '{"class":"combobox"}', 1),
            $this->campo(4, 'Cajero por defecto', 'select', 'cajero_default_id', 'model_App\\User', 'null', '{"class":"combobox"}', 0),
            $this->campo(5, 'Cliente por defecto', 'select', 'cliente_default_id', 'model_App\\Ventas\\Cliente', 'null', '{"class":"combobox"}', 1),
            $this->campo(6, 'Tipo Documento por defecto', 'select', 'tipo_doc_app_default_id', 'model_App\\Core\\TipoDocApp', 'null', '{"class":"combobox"}', 1),
            $this->campo(7, 'Serial Maquina', 'bsText', 'serial_maquina', '', 'null', '', 0),
            $this->campo(8, 'Formato factura default', 'select', 'plantilla_factura_pos_default', '{"plantilla_factura":"Basico","plantilla_factura_2":"Visual","plantilla_factura_3":"Logo ancho","plantilla_factura_remision_cocina":"Factura + RM cocina"}', 'plantilla_factura', '', 0),
            $this->campo(9, 'Direccion', 'bsText', 'direccion', '', 'null', '', 0),
            $this->campo(10, 'Telefono', 'bsText', 'telefono', '', 'null', '', 0),
            $this->campo(11, 'Email', 'bsEmail', 'email', '', 'null', '', 0),
            $this->campo(12, 'Maneja Impoconsumo', 'select', 'maneja_impoconsumo', '{"0":"No","1":"Si"}', '0', '', 0),
            $this->campo(13, 'Detalle', 'bsTextArea', 'detalle', '', 'null', '', 0),
            $this->campo(14, 'Impresora APM Caja', 'select', 'impresora_principal_por_defecto', 'model_App\\Ventas\\ApmPrinterDevice', 'null', '{"class":"form-control"}', 0),
            $this->campo(15, 'Impresora APM Cocina', 'select', 'impresora_cocina_por_defecto', 'model_App\\Ventas\\ApmPrinterDevice', 'null', '{"class":"form-control"}', 0),
            $this->campo(16, 'Impresion directa en Caja (Factura)', 'select', 'imprimir_factura_automaticamente', '{"0":"No","1":"Si","2":"Preguntar"}', '0', '', 0),
            $this->campo(17, 'Impresion directa en cocina (Comanda)', 'select', 'enviar_impresion_directamente_a_la_impresora', '{"0":"No","1":"Si","2":"Preguntar"}', '0', '', 0),
            $this->campo(18, 'Usar complemento JSPrintManager', 'select', 'usar_complemento_JSPrintManager', '{"0":"No","1":"Si"}', '0', '', 0),
            $this->campo(19, 'Crear ensamble de recetas en la acumulacion', 'select', 'crear_ensamble_de_recetas', '{"0":"No","1":"Si"}', '0', '', 0),
        ];
    }

    protected function shouldUpdateExistingField($fieldName)
    {
        return in_array($fieldName, [
            'impresora_principal_por_defecto',
            'impresora_cocina_por_defecto',
            'imprimir_factura_automaticamente',
            'enviar_impresion_directamente_a_la_impresora',
            'usar_complemento_JSPrintManager',
            'plantilla_factura_pos_default',
            'maneja_impoconsumo',
            'crear_ensamble_de_recetas',
        ]);
    }

    protected function getCampoRelacionadoId($modeloId, $fieldName)
    {
        return (int)DB::table('sys_modelo_tiene_campos')
            ->join('sys_campos', 'sys_campos.id', '=', 'sys_modelo_tiene_campos.core_campo_id')
            ->where('sys_modelo_tiene_campos.core_modelo_id', $modeloId)
            ->where('sys_campos.name', $fieldName)
            ->orderBy('sys_modelo_tiene_campos.orden')
            ->orderBy('sys_modelo_tiene_campos.id')
            ->value('sys_modelo_tiene_campos.core_campo_id');
    }

    protected function removeDuplicateRelations($modeloId)
    {
        $fieldNames = array_map(function ($campo) {
            return $campo['name'];
        }, $this->getCampos());

        foreach ($fieldNames as $fieldName) {
            $relations = DB::table('sys_modelo_tiene_campos')
                ->join('sys_campos', 'sys_campos.id', '=', 'sys_modelo_tiene_campos.core_campo_id')
                ->where('sys_modelo_tiene_campos.core_modelo_id', $modeloId)
                ->where('sys_campos.name', $fieldName);

            $relations = $relations->orderBy('sys_modelo_tiene_campos.orden')
                ->orderBy('sys_modelo_tiene_campos.id')
                ->select('sys_modelo_tiene_campos.id')
                ->get();

            $keepFirst = true;
            foreach ($relations as $relation) {
                if ($keepFirst) {
                    $keepFirst = false;
                    continue;
                }

                DB::table('sys_modelo_tiene_campos')->where('id', $relation->id)->delete();
            }
        }
    }

    protected function campo($orden, $descripcion, $tipo, $name, $opciones, $value, $atributos, $requerido)
    {
        return [
            'orden' => $orden,
            'descripcion' => $descripcion,
            'tipo' => $tipo,
            'name' => $name,
            'opciones' => $opciones,
            'value' => $value,
            'atributos' => $atributos,
            'definicion' => '',
            'requerido' => $requerido,
            'editable' => 1,
            'unico' => 0,
        ];
    }
}
