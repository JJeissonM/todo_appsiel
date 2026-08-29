<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TurnosAdminCrudSeeder extends Seeder
{
    public function run()
    {
        if (!$this->hasRequiredTables()) {
            return;
        }

        DB::transaction(function () {
            $models = $this->models();
            foreach ($models as $key => $definition) {
                $modelId = $this->seedModel($definition['model']);
                $this->seedFields($modelId, $definition['fields']);
                $this->seedPermission($modelId, $definition['permission']);
            }
        });
    }

    protected function hasRequiredTables()
    {
        foreach (array(
            'core_turno_configuraciones', 'core_turnos_operativos', 'core_turno_eventos',
            'sys_modelos', 'sys_campos', 'sys_modelo_tiene_campos',
            'sys_aplicaciones', 'permissions', 'roles', 'role_has_permissions'
        ) as $table) {
            if (!Schema::hasTable($table)) {
                return false;
            }
        }
        return true;
    }

    protected function models()
    {
        $moduleOptions = array('*' => 'Todos los módulos');
        foreach ((array)config('turnos.modules', array()) as $module => $settings) {
            $moduleOptions[$module] = $module . (!empty($settings['integrated']) ? '' : ' (pendiente)');
        }

        return array(
            'configuration' => array(
                'model' => $this->model(
                    'Configuración de turnos operativos', 'TurnoConfiguracion', 'App\\Core\\TurnoConfiguracion',
                    'web/create', 'web/id_fila/edit', 'web/id_fila',
                    'turnos/configuraciones', 'turnos/configuraciones/id_fila/eliminar'
                ),
                'fields' => array(
                    $this->field(1, 'Empresa', 'hidden', 'core_empresa_id', '', 'null', 1, 0),
                    $this->field(2, 'Módulo', 'select', 'modulo', json_encode($moduleOptions), '*', 1, 1),
                    $this->field(3, 'Tipo de contexto', 'select', 'contexto_tipo', '{"pdv":"PDV","*":"Empresa / todos"}', 'pdv', 1, 1),
                    $this->field(4, 'Contexto operativo', 'select', 'contexto_id', '{}', '0', 1, 1),
                    $this->field(5, 'Modo de operación', 'select', 'modo', '{"TRADICIONAL":"TRADICIONAL","TURNOS":"TURNOS"}', 'TRADICIONAL', 1, 1),
                    $this->field(6, 'Creado por', 'hidden', 'creado_por', '', 'null', 0, 0),
                    $this->field(7, 'Modificado por', 'hidden', 'modificado_por', '', 'null', 0, 0),
                ),
                'permission' => array(
                    'name' => 'turnos.configuraciones.gestionar',
                    'description' => 'Configuración de turnos operativos',
                    'icon' => 'sliders',
                    'order' => 84,
                ),
            ),
            'turns' => array(
                'model' => $this->model(
                    'Turnos operativos', 'TurnoOperativo', 'App\\Core\\TurnoOperativo',
                    '', '', 'web/id_fila', '', ''
                ),
                'fields' => array(
                    $this->field(1, 'Empresa', 'select', 'core_empresa_id', 'model_App\\Core\\Empresa', 'null', 1, 0),
                    $this->field(2, 'Código', 'bsText', 'codigo', '', 'null', 1, 0),
                    $this->field(3, 'Tipo de contexto', 'bsText', 'contexto_tipo', '', 'null', 1, 0),
                    $this->field(4, 'ID del contexto', 'bsText', 'contexto_id', '', 'null', 1, 0),
                    $this->field(5, 'PDV', 'select', 'pdv_id', 'model_App\\VentasPos\\Pdv', 'null', 0, 0),
                    $this->field(6, 'Caja', 'select', 'teso_caja_id', 'model_App\\Tesoreria\\TesoCaja', 'null', 0, 0),
                    $this->field(7, 'Fecha operativa', 'bsDate', 'fecha_operativa', '', 'null', 1, 0),
                    $this->field(8, 'Abierto en', 'bsText', 'abierto_en', '', 'null', 1, 0),
                    $this->field(9, 'Cerrado en', 'bsText', 'cerrado_en', '', 'null', 0, 0),
                    $this->field(10, 'Abierto por', 'select', 'abierto_por', 'model_App\\User', 'null', 0, 0),
                    $this->field(11, 'Cerrado por', 'select', 'cerrado_por', 'model_App\\User', 'null', 0, 0),
                    $this->field(12, 'Saldo inicial', 'bsText', 'saldo_inicial', '', '0', 1, 0),
                    $this->field(13, 'Saldo de cierre', 'bsText', 'saldo_cierre', '', 'null', 0, 0),
                    $this->field(14, 'Estado', 'bsText', 'estado', '', 'null', 1, 0),
                    $this->field(15, 'Observaciones', 'bsTextArea', 'observaciones', '', 'null', 0, 0),
                ),
                'permission' => array(
                    'name' => 'turnos.operativos.consultar',
                    'description' => 'Turnos operativos',
                    'icon' => 'clock-o',
                    'order' => 85,
                ),
            ),
            'events' => array(
                'model' => $this->model(
                    'Eventos de turnos operativos', 'TurnoEvento', 'App\\Core\\TurnoEvento',
                    '', '', 'web/id_fila', '', ''
                ),
                'fields' => array(
                    $this->field(1, 'Turno operativo', 'select', 'turno_operativo_id', 'model_App\\Core\\TurnoOperativo', 'null', 1, 0),
                    $this->field(2, 'Tipo de evento', 'bsText', 'tipo', '', 'null', 1, 0),
                    $this->field(3, 'Estado anterior', 'bsText', 'estado_anterior', '', 'null', 0, 0),
                    $this->field(4, 'Estado nuevo', 'bsText', 'estado_nuevo', '', 'null', 0, 0),
                    $this->field(5, 'Entidad afectada', 'bsText', 'entidad_tipo', '', 'null', 0, 0),
                    $this->field(6, 'ID entidad', 'bsText', 'entidad_id', '', 'null', 0, 0),
                    $this->field(7, 'Usuario', 'select', 'usuario_id', 'model_App\\User', 'null', 0, 0),
                    $this->field(8, 'Motivo', 'bsTextArea', 'motivo', '', 'null', 0, 0),
                    $this->field(9, 'Metadatos', 'bsTextArea', 'datos', '', 'null', 0, 0),
                ),
                'permission' => array(
                    'name' => 'turnos.eventos.consultar',
                    'description' => 'Auditoría de turnos',
                    'icon' => 'history',
                    'order' => 86,
                ),
            ),
        );
    }

    protected function model($description, $model, $namespace, $create, $edit, $show, $form, $delete)
    {
        return array(
            'descripcion' => $description,
            'modelo' => $model,
            'name_space' => $namespace,
            'modelo_relacionado' => '',
            'url_crear' => $create,
            'url_edit' => $edit,
            'url_print' => '',
            'url_ver' => $show,
            'enlaces' => '',
            'url_estado' => '',
            'url_eliminar' => $delete,
            'controller_complementario' => '',
            'url_form_create' => $form,
            'home_miga_pan' => 'configuracion,Configuración',
            'ruta_storage_imagen' => '',
            'ruta_storage_archivo_adjunto' => '',
        );
    }

    protected function field($order, $description, $type, $name, $options, $value, $required, $editable)
    {
        return array(
            'orden' => $order,
            'descripcion' => $description,
            'tipo' => $type,
            'name' => $name,
            'opciones' => $options,
            'value' => $value,
            'atributos' => '',
            'definicion' => '',
            'requerido' => $required,
            'editable' => $editable,
            'unico' => 0,
        );
    }

    protected function seedModel(array $data)
    {
        $now = date('Y-m-d H:i:s');
        $model = DB::table('sys_modelos')->where('name_space', $data['name_space'])->first();
        $data = $this->onlyExistingColumns('sys_modelos', $data);
        $data['updated_at'] = $now;
        if ($model) {
            DB::table('sys_modelos')->where('id', $model->id)->update($data);
            return (int)$model->id;
        }
        $data['created_at'] = $now;
        return (int)DB::table('sys_modelos')->insertGetId($data);
    }

    protected function seedFields($modelId, array $fields)
    {
        foreach ($fields as $field) {
            $order = $field['orden'];
            unset($field['orden']);
            $fieldId = DB::table('sys_campos')
                ->where('name', $field['name'])->where('descripcion', $field['descripcion'])->value('id');
            $field['updated_at'] = date('Y-m-d H:i:s');
            if ($fieldId) {
                DB::table('sys_campos')->where('id', $fieldId)->update($field);
            } else {
                $field['created_at'] = date('Y-m-d H:i:s');
                $fieldId = DB::table('sys_campos')->insertGetId($field);
            }

            $relation = DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modelId)->where('core_campo_id', $fieldId);
            if ($relation->exists()) {
                $relation->update(array('orden' => $order));
            } else {
                DB::table('sys_modelo_tiene_campos')->insert(array(
                    'core_modelo_id' => $modelId, 'core_campo_id' => $fieldId, 'orden' => $order,
                ));
            }
        }
    }

    protected function seedPermission($modelId, array $definition)
    {
        $appId = (int)DB::table('sys_aplicaciones')->where('descripcion', 'Configuración')->value('id');
        if ($appId === 0) {
            $appId = (int)DB::table('sys_aplicaciones')->where('descripcion', 'Configuracion')->value('id');
        }
        if ($appId === 0) {
            $appId = 7;
        }
        $permission = Permission::firstOrNew(array('name' => $definition['name']));
        $permission->core_app_id = $appId;
        $permission->modelo_id = $modelId;
        $permission->descripcion = $definition['description'];
        $permission->url = 'web';
        $permission->parent = 0;
        $permission->orden = $definition['order'];
        $permission->enabled = 0;
        $permission->fa_icon = $definition['icon'];
        $permission->save();

        foreach (array('SuperAdmin', 'Administrador') as $roleName) {
            $role = Role::firstOrCreate(array('name' => $roleName));
            $exists = DB::table('role_has_permissions')
                ->where('role_id', $role->id)->where('permission_id', $permission->id)->exists();
            if (!$exists) {
                $role->givePermissionTo($permission);
            }
        }
    }

    protected function onlyExistingColumns($table, array $data)
    {
        $result = array();
        foreach ($data as $column => $value) {
            if (Schema::hasColumn($table, $column)) {
                $result[$column] = $value;
            }
        }
        return $result;
    }
}
