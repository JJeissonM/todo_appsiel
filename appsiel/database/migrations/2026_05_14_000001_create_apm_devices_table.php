<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateApmDevicesTable extends Migration
{
    protected $modelNamespace = 'App\\Ventas\\ApmDevice';
    protected $modelTable = 'apm_devices';

    public function up()
    {
        if (!Schema::hasTable('apm_devices')) {
            Schema::create('apm_devices', function (Blueprint $table) {
                $table->increments('id');
                $table->string('device_type', 30)->default('printer');
                $table->string('device_id', 120)->unique();
                $table->string('device_name', 160);
                $table->string('ip_address', 80)->nullable();
                $table->unsignedInteger('paper_width_mm')->nullable();
                $table->string('code_page', 20)->nullable();
                $table->boolean('beep_after_print')->default(false);
                $table->boolean('open_drawer_after_print')->default(false);
                $table->boolean('cut_after_print')->default(false);
                $table->string('serial_port', 80)->nullable();
                $table->unsignedInteger('baud_rate')->nullable();
                $table->unsignedInteger('data_bits')->nullable();
                $table->string('parity', 20)->nullable();
                $table->string('stop_bits', 20)->nullable();
                $table->string('estado', 20)->default('Activo');
                $table->timestamps();

                $table->index('device_type', 'idx_apm_devices_type');
                $table->index('estado', 'idx_apm_devices_estado');
            });
        }

        $this->registerCrudModel();
    }

    public function down()
    {
        $this->unregisterCrudModel();
        Schema::dropIfExists('apm_devices');
    }

    protected function registerCrudModel()
    {
        if (!Schema::hasTable('sys_modelos')) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $modeloId = DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');

        if (!$modeloId) {
            $modelData = [
                'descripcion' => 'Dispositivos APM',
                'modelo' => $this->modelTable,
                'name_space' => $this->modelNamespace,
                'modelo_relacionado' => '',
                'url_crear' => '',
                'url_edit' => '',
                'url_print' => '',
                'url_ver' => '',
                'enlaces' => '',
                'url_estado' => '',
                'url_eliminar' => 'web_eliminar/id_fila',
                'controller_complementario' => '',
                'url_form_create' => '',
                'home_miga_pan' => '',
                'ruta_storage_imagen' => '',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (Schema::hasColumn('sys_modelos', 'ruta_storage_archivo_adjunto')) {
                $modelData['ruta_storage_archivo_adjunto'] = '';
            }

            $modeloId = DB::table('sys_modelos')->insertGetId($modelData);
        }

        $this->registerCrudFields($modeloId, $now);
        $this->registerPermission($modeloId, $now);
    }

    protected function registerCrudFields($modeloId, $now)
    {
        if (!Schema::hasTable('sys_campos') || !Schema::hasTable('sys_modelo_tiene_campos')) {
            return;
        }

        $fields = [
            ['name' => 'device_type', 'descripcion' => 'Tipo de dispositivo', 'tipo' => 'select', 'opciones' => '{"printer":"Impresora","scale":"Bascula"}', 'value' => 'printer', 'orden' => 1, 'requerido' => 1],
            ['name' => 'device_id', 'descripcion' => 'ID APM del dispositivo', 'tipo' => 'bsText', 'opciones' => '', 'value' => '', 'orden' => 2, 'requerido' => 1],
            ['name' => 'device_name', 'descripcion' => 'Nombre', 'tipo' => 'bsText', 'opciones' => '', 'value' => '', 'orden' => 3, 'requerido' => 1],
            ['name' => 'ip_address', 'descripcion' => 'Direccion IP impresora', 'tipo' => 'bsText', 'opciones' => '', 'value' => '', 'orden' => 4, 'requerido' => 0],
            ['name' => 'paper_width_mm', 'descripcion' => 'Ancho papel (mm)', 'tipo' => 'select', 'opciones' => '{"":"", "80":"80 mm", "58":"58 mm"}', 'value' => '80', 'orden' => 5, 'requerido' => 0],
            ['name' => 'code_page', 'descripcion' => 'Code page (ESC/POS)', 'tipo' => 'bsText', 'opciones' => '', 'value' => '49', 'orden' => 6, 'requerido' => 0],
            ['name' => 'beep_after_print', 'descripcion' => 'Pitar al final', 'tipo' => 'select', 'opciones' => '{"0":"No","1":"Si"}', 'value' => '0', 'orden' => 7, 'requerido' => 0],
            ['name' => 'open_drawer_after_print', 'descripcion' => 'Abrir cajon al final', 'tipo' => 'select', 'opciones' => '{"0":"No","1":"Si"}', 'value' => '0', 'orden' => 8, 'requerido' => 0],
            ['name' => 'cut_after_print', 'descripcion' => 'Cortar al final', 'tipo' => 'select', 'opciones' => '{"0":"No","1":"Si"}', 'value' => '0', 'orden' => 9, 'requerido' => 0],
            ['name' => 'serial_port', 'descripcion' => 'Puerto COM bascula', 'tipo' => 'bsText', 'opciones' => '', 'value' => '', 'orden' => 10, 'requerido' => 0],
            ['name' => 'baud_rate', 'descripcion' => 'Baud rate', 'tipo' => 'bsText', 'opciones' => '', 'value' => '9600', 'orden' => 11, 'requerido' => 0],
            ['name' => 'data_bits', 'descripcion' => 'Data bits', 'tipo' => 'select', 'opciones' => '{"":"", "7":"7", "8":"8"}', 'value' => '8', 'orden' => 12, 'requerido' => 0],
            ['name' => 'parity', 'descripcion' => 'Paridad', 'tipo' => 'select', 'opciones' => '{"None":"None","Odd":"Odd","Even":"Even","Mark":"Mark","Space":"Space"}', 'value' => 'None', 'orden' => 13, 'requerido' => 0],
            ['name' => 'stop_bits', 'descripcion' => 'Stop bits', 'tipo' => 'select', 'opciones' => '{"One":"One","Two":"Two"}', 'value' => 'One', 'orden' => 14, 'requerido' => 0],
            ['name' => 'estado', 'descripcion' => 'Estado', 'tipo' => 'select', 'opciones' => '{"Activo":"Activo","Inactivo":"Inactivo"}', 'value' => 'Activo', 'orden' => 15, 'requerido' => 1],
        ];

        foreach ($fields as $field) {
            $campoId = DB::table('sys_campos')->where('name', $field['name'])->value('id');

            if (!$campoId) {
                $campoId = DB::table('sys_campos')->insertGetId([
                    'tipo' => $field['tipo'],
                    'name' => $field['name'],
                    'descripcion' => $field['descripcion'],
                    'opciones' => $field['opciones'],
                    'value' => $field['value'],
                    'atributos' => '{"class":"form-control"}',
                    'definicion' => '',
                    'requerido' => $field['requerido'],
                    'editable' => 1,
                    'unico' => $field['name'] == 'device_id' ? 1 : 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if (in_array($field['name'], ['ip_address', 'paper_width_mm', 'code_page', 'beep_after_print', 'open_drawer_after_print', 'cut_after_print', 'ip_address'])) {
                continue;
            }

            $exists = DB::table('sys_modelo_tiene_campos')
                ->where('core_modelo_id', $modeloId)
                ->where('core_campo_id', $campoId)
                ->exists();

            if (!$exists) {
                DB::table('sys_modelo_tiene_campos')->insert([
                    'orden' => $field['orden'],
                    'core_modelo_id' => $modeloId,
                    'core_campo_id' => $campoId,
                ]);
            }
        }
    }

    protected function registerPermission($modeloId, $now)
    {
        if (!Schema::hasTable('permissions')) {
            return;
        }

        $coreAppId = 13;
        if (Schema::hasTable('sys_aplicaciones')) {
            $foundAppId = DB::table('sys_aplicaciones')->where('descripcion', 'LIKE', '%Ventas%')->value('id');
            if ($foundAppId) {
                $coreAppId = (int) $foundAppId;
            }
        }

        if (!DB::table('permissions')->where('name', 'vtas_apm_devices')->exists()) {
            DB::table('permissions')->insert([
                'core_app_id' => $coreAppId,
                'modelo_id' => $modeloId,
                'name' => 'vtas_apm_devices',
                'descripcion' => 'Dispositivos APM',
                'url' => 'web',
                'parent' => 0,
                'orden' => 99,
                'enabled' => 0,
                'fa_icon' => 'plug',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    protected function unregisterCrudModel()
    {
        if (!Schema::hasTable('sys_modelos')) {
            return;
        }

        $modeloId = DB::table('sys_modelos')->where('name_space', $this->modelNamespace)->value('id');

        if (!$modeloId) {
            return;
        }

        if (Schema::hasTable('permissions')) {
            DB::table('permissions')->where('name', 'vtas_apm_devices')->delete();
        }

        if (Schema::hasTable('sys_modelo_tiene_campos')) {
            DB::table('sys_modelo_tiene_campos')->where('core_modelo_id', $modeloId)->delete();
        }

        DB::table('sys_modelos')->where('id', $modeloId)->delete();
    }
}
